<?php
    session_start();

    # Session variables
    $_SESSION['whoami']; // String
    $_SESSION['logged']; // Boolean
    $_SESSION['activedir']; // String
    $_SESSION['step1']; // Integer
    $_SESSION['columns']; // Array
    $_SESSION['count1']; // Integer
    $_SESSION['count2']; // Integer
    $_SESSION['file']; //String
    $_SESSION['userInput1']; // String
    $_SESSION['userInput2']; // String
    $_SESSION['showPLOTS']; // Boolean
    $_SESSION['zipFile']; // String (link)
    $_SESSION['pngFile']; // String
    $_SESSION['step2']; // Integer
    $_SESSION['category']; //String
    $_SESSION['array']; // Array
    $_SESSION['page']; // Integer


    # Login function
    if (isset($_POST['login']))
    {
        $creds = $_POST['email'] . ',' . $_POST['password'];
        $read = fopen('users/userInfo.csv', 'r');
        $found = FALSE;
        while (!feof($read))
        {
            $line = fgets($read);
            if (strcmp($line, $creds) == 1 || strcmp($line, $creds) == 0)
            {
                $_SESSION['logged'] = TRUE;
                $_SESSION['whoami'] = $_POST['email'];
                $_COOKIE['whoami'] = $_POST['email'];
                $_SESSION['step1'] = 0;
                $_SESSION['step2'] = 0;
                $found = TRUE;
                $_SESSION['page'] = 1;
            }
        }
    }

    # Logout function
    if (isset($_POST['logout']))
    {
        $_SESSION['logged'] = FALSE;
        $_SESSION['whoami'] = null;
        $_SESSION['page'] = -1;
        $_SESSION['step1'] = -1;
        $_SESSION['step2'] = -1;
        session_destroy();
    }

    # Paging function
    if (isset($_POST['page1']))
    {
        $_SESSION['page'] = 1;
    }
    if (isset($_POST['page2']))
    {
        $_SESSION['page'] = 2;
    }
    if (isset($_POST['page3']))
    {
        $_SESSION['page'] = 3;
    }
    if (isset($_POST['page4']))
    {
        $_SESSION['page'] = 4;
    }
    if (isset($_POST['page5']))
    {
        $_SESSION['page'] = 5;
    }


    # Upload function
    if (isset($_POST['upload']))
    {
        $target_dir = 'users/dirs/' . $_SESSION['whoami'] . '/files/' . $_FILES['fileToUpload']['name'];
        move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_dir);
        shell_exec('dos2unix ' . $target_dir);
    }

    # Delete function
    if (isset($_POST['delete']))
    {
        foreach ($_POST['filetoDel'] as $file)
        {
            unlink($file);
        }
    }

    # Restart step 1
    if (isset($_POST['restart1']))
    {
        $_SESSION['step1'] = 0;
    }

    # Hypothetical Calculation step 1
    if (isset($_POST['nextStep1']))
    {
        $read = fopen($_POST['filetoCalc'], 'r');
        $_SESSION['file'] = $_POST['filetoCalc'];
        $temp = explode(',', str_replace('^M', '', fgets($read)));
        $_SESSION['count1'] = intval($temp[0]);
        $_SESSION['count2'] = intval($temp[1]);
        $_SESSION['columns'] = explode(',', fgets($read));
        $_SESSION['step1'] = 1;
        $_SESSION['step2'] = 0;

    }

    # Hypothetical Calculation step 2
    if (isset($_POST['commit']))
    {
        // This makes it to allow tab delimited, but replaces /t with ','
        $tmp = str_replace("\t", ",", $_POST['userInput1']);
        $tmp = str_replace(' ,', ',', $_POST['userInput1']);
        $_2dArray = explode("\n", $tmp);
        $errors = FALSE;
        $limit = 1 + ($_SESSION['count2'] - $_SESSION['count1']) + 1;
        for ($x = 0; $x < sizeof($_2dArray); $x++)
        {
            $_1dArray = explode(',', $_2dArray[$x]);
            // Do not calculate if the data isn't formatted perfectly
            if (sizeof($_1dArray) != $limit)
            {
                $CalcMessage1 = "Data is ill formatted<br><br>";
                $error = TRUE;
            }
        }
        if (!$error)
        {
            $write = fopen('users/dirs/' . $_SESSION['whoami'] . '/tmp/requests', 'w');
            fwrite($write, $_SESSION['whoami'] . "\n" . $_SESSION['file'] . "\n" . $_SESSION['count1'] . "\n" .$_SESSION['count2'] . "\n" . $_POST['saveas'] . "\n" . str_replace("\t", ",", $_POST['userInput1']));
            fclose($write);
            $write2 = fopen('activeDir', 'w');
            fwrite($write2, $_SESSION['whoami']);
            fclose($write2);
            $_SESSION['step1'] = 0;
            // Run the Python script from a virtual env
            // Reminder: Ask Mrs. Casey to create the virtual env with python and modules through email
            echo shell_exec('env/bin/python src/Pearson.py 2>&1');
            $_SESSION['page'] = 3;
            $_SESSION['step2'] = 0;
        }
    }

    # Restart From 2
    if (isset($_POST['restart2']))
    {
        $_SESSION['step2'] = 0;
    }

    # Pre-existing Calculation Step 1
    if (isset($_POST['nextStep2']))
    {
        $read = fopen($_POST['filetoCalc2'], 'r');
        $_SESSION['file'] = $_POST['filetoCalc2'];
        $temp = explode(',', fgets($read));
        $_SESSION['count1'] = intval($temp[0]);
        $_SESSION['count2'] = intval($temp[1]);
        $_SESSION['columns'] = explode(',', fgets($read));
        $_SESSION['step2'] = 1;
        $_SESSION['step1'] = 0;
    }

    # Pre-existing Calculation Step 2
    if (isset($_POST['commit2']))
    {
        echo 'a';
        $tmp = $_POST['userInput2'];
        $tmp = str_replace(', ', ',', $tmp);
        $tmp = str_replace(' ,', ',', $tmp);
        $tmp = ltrim($tmp);
        $_2dArray = explode(',', $tmp);
        $errors = FALSE;
        $read = fopen($_SESSION['file'], 'r');
        fgets($read);
        fgets($read);
        $_SESSION['array'] = [];
        $count = sizeof($_2dArray);
        while (!feof($read))
        {
            $line = fgets($read);
            $line = str_replace("\n", '', $line);
            $tmp = strtolower(explode(',', $line)[0]);
            for ($x = 0; $x < sizeof($_2dArray); $x++)
            {
                $clone = 0;
                if ($tmp == strtolower($_2dArray[$x]))
                {
                    $count -= 1;
                    $toAdd = explode(',', $line);
                    echo $tmp;
                    array_push($_SESSION['array'], $toAdd);
                    continue;
                }
            }
        }
        echo 'b';
        fclose($read);
        if ($count >= 0)
        {
            echo 'c';
            $CalcMessage2 = 'The given data was not found in the file<br>';
            echo '///' . $count . '///';
        }
        else
        {
            echo 'd';
            $write = fopen('users/dirs/' . $_SESSION['whoami'] . '/tmp/requests', 'w');
            fwrite($write, $_SESSION['whoami'] . "\n" . $_SESSION['file'] . "\n" . $_SESSION['count1'] . "\n" .$_SESSION['count2'] . "\n" . $_POST['saveas2'] . "\n");
            for ($x = 0; $x < sizeof($_2dArray); $x++)
            {
                fwrite($write, $_2dArray[$x] . ',');
                for ($y = $_SESSION['count1'] - 1; $y < $_SESSION['count2']; $y++)
                {
                    fwrite($write, $_SESSION['array'][$x][$y]);
                    if ($y != $_SESSION['count2'] - 1)
                    {
                        fwrite($write, ',');
                    }
                }
                if ($x != sizeof($_2dArray) - 1)
                {
                    fwrite($write, "\n");
                }
            }
            echo 'e';
            fclose($write);
            $write2 = fopen('activeDir', 'w');
            fwrite($write2, $_SESSION['whoami']);
            fclose($write2);
            $_SESSION['step1'] = 0;
            // Run the Python script from a virtual env
            // Reminder: Ask Mrs. Casey to create the virtual env with python and modules through email
            echo shell_exec('env/bin/python src/Pearson.py 2>&1');
            $_SESSION['page'] = 3; 
            $_SESSION['step2'] = 0; 
        }
    }

    # View Commit Data
    if (isset($_POST['view']))
    {
        $write2 = fopen('activeDir', 'w');
        fwrite($write2, $_SESSION['whoami']);
        fclose($write2);
        $_SESSION['showPNG'] = TRUE;
        $_SESSION['zipOI'] = $_POST['zipfile'];
        $_SESSION['zipFile'] = '<a href="' . $_POST['zipfile'] . '" Download>Click here</a>' . ' to download data and plots.';
        echo shell_exec('env/bin/python src/Extract.py ' . $_SESSION['zipOI'] . ' ' . 'users/dirs/' . $_SESSION['whoami'] . '/results 2>&1');
    }
    if (isset($_POST['display']))
    {
        $_SESSION['pngFile'] = '<img height=25% length=25% src="' . $_POST['pngfile'] . '"><br><br>';
    }

    # Delete Commit Data
    if (isset($_POST['deleteCommit']))
    {
        unlink($_POST['deletezipfile']);
    }

    # Leave Feedback
    if (isset($_POST['leaveFB']))
    {
        $write = fopen('feedback', 'w');
        $line = '=====' . "\n" . $_SESSION['whoami'] . "\n" . $_POST['feedback'] . "\n" . '=====' . "\n";
        fwrite($write, $line);
        fclose($write);

    }
?>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <title>PHTA-v2</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <style>
        body
        {
            background-color: black;
        	font:14px/1.4 'Arial', 'Helvetica', sans-serif;
        }
        #body
        {
            background-color: white;
            padding: 0;
        }
        #body1
        {
            background-color: #C70039;
            color: white;
        }
        #body1 b
        {
            color: white;
        }
        .page
        {
            color: white;
            font-weight: bold;
            border-style: solid;
            background-color: #C70029;
            height: 30px;
            border-color: #C70039;
        }
        .page:hover
        {
            background-color: #73000a;
        }
        h3, b, hr
        {
            color: #73000a;
        }
        .img
        {
            margin: 20px;
        }
    </style>
    <body>
        <div id="body">
            <h1 style="display:inline; font-weight:normal">
                <b>PH</b>enotype <b>T</b>ranscriptomic <b>A</b>ssociation <b>Calculator</b>
            </h1>
            <img id="img2" src="img/nsf-logo.png" height="59px" width="59px" style="display: inline; float:right">
            <img src="img/usc.jpeg" height="59px" width="59px" style="display: inline; float:right">
            </br>
            Work of Dr. Homayoun Valafar, Dr. Hippokratis Kiaris, Youwen Zhang, Naga Venkata Sai Jagjit (JJ) Satti
        </div>
        <div id="body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Login menu
                    if (!$_SESSION['logged'])
                    {
                        echo '<h3>Login:</h3><hr>' . 
                        'Email: <input type="email" name="email" Required><br><br>' .
                        'Password: <input type="text" name="password" Required><br><br>' . 
                        '<input type="submit" name="login" value="Login"><br><br>';
                        echo '<a href="create.php">Click here</a> to create an account.';
                    }
                ?>
            </form>
        </div>
        <div id="body1">
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Navigation tools
                    if ($_SESSION['logged'])
                    {
                        echo '<b>Profile:</b> ' . $_SESSION['whoami'] .
                        '<input type="submit" name="logout" class="page" value="Logout">' . 
                        '<input type="submit" class="page" name="page1" value="Manage Files">' . 
                        '<input type="submit" name="page2" class="page" value="Analyze">' . 
                        '<input type="submit" name="page3" class="page" value="View Results">' . 
                        '<input type="submit" name="page4" class="page" value="Leave Feedback">' . 
                        '<input type="submit" name="page5" class="page" value="Guide">';
                    }
                ?>
            </form>
        </div>
        <div id="body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Upload menu
                    if ($_SESSION['logged'] && $_SESSION['page'] == 1)
                    {
                        echo '<h3>Upload:</h3><hr>' .
                        '<input type="file" name="fileToUpload"><br><br>' .
                        '<input type="submit" name="upload" value="Upload">';
                    }
                ?>
            </form>
            <form action="" method="post" enctype="mutlipart/form-data">
                <?php
                    // Delete menu
                    if ($_SESSION['logged'] && $_SESSION['page'] == 1)
                    {
                        echo '<h3>Delete:</h3><hr>' .
                        'Select the file(s) to delete:<br><br>' .
                        '<select name="filetoDel[]" multiple="multiple">';
                        $files = scandir('users/dirs/' . $_SESSION['whoami'] . '/files');
                        for ($x = 2; $x < sizeof($files); $x++)
                        {
                            echo '<option value="' .  'users/dirs/' . $_SESSION['whoami'] . '/files/' . $files[$x] . '">' . $files[$x] . '</option>';
                        }
                        echo '</select><br><br>' . 
                        '<input type="submit" name="delete" value="Delete">';
                    }
                ?>
            </form>
            <form action="" method="post" enctype="mutlipart/form-data">
                <?php
                    // Inventory
                    if ($_SESSION['logged'] && $_SESSION['page'] == 1)
                    {
                        echo '<h3>Inventory:</h3><hr>' . 
                        '<table border="1">' . 
                            '<tr><th>File:</th><th>Date Uploaded:</th><tr>';
                        $files = scandir('users/dirs/' . $_SESSION['whoami'] . '/files');
                        for ($x = 2; $x < sizeof($files); $x++)
                        {
                            echo '<tr><td>' . $files[$x] . '</td><td>' . date("d F Y", filemtime('users/dirs/' . $_SESSION['whoami'] . '/files/' . $files[$x])) . '</td></tr>';
                        } 
                        echo '</table>';
                    }
                ?>
            </form>
        </div>
        <div id="body">
            <?php
                if ($_SESSION['logged'] && $_SESSION['page'] == 2)
                {
                    echo '<h3>Analyze with Gene Expression Data:</h3><hr>';
                }
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Restart Form 2
                    if ($_SESSION['step2'] > 0 && $_SESSION['page'] == 2)
                    {
                        echo '<input type="submit" name="restart2" value="Restart Form">';
                    }
                ?>
            </form>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Pre-existing Calculation Step 1
                    if ($_SESSION['logged'] && $_SESSION['step2'] == 0 && $_SESSION['page'] == 2)
                    {
                        echo 'Select the file(s) to calculate with:<br><br>' .
                        '<select name="filetoCalc2">';
                        $files = scandir('users/dirs/' . $_SESSION['whoami'] . '/files');
                        for ($x = 2; $x < sizeof($files); $x++)
                        {
                            echo '<option value="' .  'users/dirs/' . $_SESSION['whoami'] . '/files/' . $files[$x] . '">' . $files[$x] . '</option>';
                        }
                        echo '</select><br><br>' . 
                        '<input type="submit" name="nextStep2" value="Next Step">';
                    }
                ?>
            </form>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Pre-existing Calculation Step 2
                    if ($_SESSION['logged'] && $_SESSION['step2'] == 1 && $_SESSION['page'] == 2)
                    {
                        echo $CalcMessage2;
                        echo 'The file requires all <b>' . $_SESSION['columns'][0] . '</b> of interest:' . 
                        '<h6>Input must be comma delimited</h6>' . 
                        '<textarea name="userInput2" rows="5" cols="50" Required></textarea><br><br>' .
                        'Save commit as: ' . 
                        '<input type="text" name="saveas2"><br><br>' . 
                        '<input type="submit" name="commit2" value="Generate Data">';
                    }
                ?>
            </form>
            <?php
                if ($_SESSION['logged']  && $_SESSION['page'] == 2)
                {
                    echo '<h3>Analyze with Quantitative Data:</h3><hr>';
                }
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Restart Form 1
                    if ($_SESSION['step1'] > 0 && $_SESSION['page'] == 2)
                    {
                        echo '<input type="submit" name="restart1" value="Restart Form">';
                    }
                ?>
            </form>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Hypothetical Calculation Step 1
                    if ($_SESSION['logged'] && $_SESSION['step1'] == 0 && $_SESSION['page'] == 2)
                    {
                        echo 'Select the file(s) to calculate with:<br><br>' .
                        '<select name="filetoCalc">';
                        $files = scandir('users/dirs/' . $_SESSION['whoami'] . '/files');
                        for ($x = 2; $x < sizeof($files); $x++)
                        {
                            echo '<option value="' .  'users/dirs/' . $_SESSION['whoami'] . '/files/' . $files[$x] . '">' . $files[$x] . '</option>';
                        }
                        echo '</select><br><br>' . 
                        '<input type="submit" name="nextStep1" value="Next Step">';
                    }
                ?>
            </form>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Hypothetical Calculation Step 2
                    if ($_SESSION['logged'] && $_SESSION['step1'] == 1 && $_SESSION['page'] == 2)
                    {
                        echo $CalcMessage1;
                        echo 'The file requires the input in the following manner:' . 
                        '<h6>' . 
                        $_SESSION['columns'][0] . ',';
                        for ($x = $_SESSION['count1'] - 1; $x <= $_SESSION['count2'] - 1; $x++)
                        {
                            echo $_SESSION['columns'][$x];
                            if ($x != $_SESSION['count2'] - 1)
                            {
                                echo ',';
                            }
                        }
                        echo '</h6>' . 
                        '<textarea name="userInput1" rows="5" cols="50" Required></textarea><br><br>' .
                        'Save commit as: ' . 
                        '<input type="text" name="saveas"><br><br>' . 
                        '<input type="submit" name="commit" value="Generate Data">';
                    }
                ?>
            </form>
        </div>
        <div id="body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Show commit history
                    if ($_SESSION['logged'] && $_SESSION['page'] == 3)
                    { 
                        echo '<h3>Access Data:</h3><hr>' .
                        'Select the save to view:<br><br>' .
                        '<select name="zipfile">';
                        $files = scandir('users/dirs/' . $_SESSION['whoami'] . '/zip');
                        for ($x = 2; $x < sizeof($files); $x++)
                        {
                            echo '<option value="' .  'users/dirs/' . $_SESSION['whoami'] . '/zip/' . $files[$x] . '">' . $files[$x] . '</option>';
                        }
                        echo '</select><br><br>' . 
                        '<input type="submit" name="view" value="View Data">';
                    }
                ?>
            </form>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Show the commit download and option to scroll photos
                    if ($_SESSION['logged'] && $_SESSION['showPNG'] && $_SESSION['page'] == 3)
                    {
                        echo '<h3>View Plots:</h3><hr>' . 
                        $_SESSION['pngFile'] .
                        'Select plot for display:<br><br>' . 
                        '<select name="pngfile">';
                        $pngs = scandir('users/dirs/' . $_SESSION['whoami'] . '/results');
                        for ($x = 2; $x < sizeof($pngs); $x++)
                        {
                            echo '<option value="' .  'users/dirs/' . $_SESSION['whoami'] . '/results/' . $pngs[$x] . '">' . $pngs[$x] . '</option>';
                        }
                        echo '</select><br><br>' . 
                        '<input type="submit" name="display" value="Display Plot"><br><br>' . 
                        $_SESSION['zipFile'];
                    }
                ?>
            </form>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Give the option to delete a previous commit
                    if ($_SESSION['logged'] && $_SESSION['page'] == 3)
                    {
                        echo '<h3>Delete Data:</h3><hr>' .
                        'Select the save to delete:<br><br>' .
                        '<select name="deletezipfile">';
                        $files = scandir('users/dirs/' . $_SESSION['whoami'] . '/zip');
                        for ($x = 2; $x < sizeof($files); $x++)
                        {
                            echo '<option value="' .  'users/dirs/' . $_SESSION['whoami'] . '/zip/' . $files[$x] . '">' . $files[$x] . '</option>';
                        }
                        echo '</select><br><br>' . 
                        '<input type="submit" name="deleteCommit" value="Delete Data">';
                    }
                ?>
            </form>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Allow for feedback 
                    if ($_SESSION['logged'] && $_SESSION['page'] == 4)
                    {
                        echo '<h3>Leave Feedback:</h3><hr>' . 
                        'Please leave any comments regarding any improvements to PHTA or errors encounters while using PHTA:<br><br>' .
                        '<textarea name="feedback" rows="5" cols="50" Required></textarea><br><br>' .
                        '<input type="submit" name="leaveFB" value="Submit">';
                    }
                ?>
            </form>
        </div>
        <div id="body">
            <?php
                if ($_SESSION['logged'] && $_SESSION['page'] == 5)
                {
                    echo '<h3>File Format Expected:</h3><hr> 
                    When PHTA is used process the files to calculate data, PHTA expects the files to be formatted in a particular format...<br>
                    <b>1 )</b> The file should be a <b>CSV</b> file.<br>
                    <b>2 )</b> The first line of the csv file should contain 2 numbers, <b>X</b> and <b>Y</b>. <b>X</b> and <b>Y</b> indicates that in the CSV file will contain numerical data between columns <b>X</b> and <b>Y</b>, inclusively.<br>
                    <b>3 )</b> The second line of the csv file should contain the name of column\'s containing the data<br>
                    <b>4 )</b> The first column shouldn\'t contain any numerical data. This column will contain a string that PHTA uses to identify each row of Gene Expression Data.
                    <img class="img" height="40%" length="40%" src="img/sampleData1.png">
                    <h6>Sample Data to test out PHTA:</h6>
                    <a href="sampledata/sampledata1.csv" download>sampledata1.csv</a><br>
                    <a href="sampledata/sampledata2.csv" download>sampledata2.csv</a>
                    <h3>Analyzing with Gene Expression Data</h3><hr>
                    <b>1 )</b> Select the csv file containing your data of interest. The file used in the example is "sampledata1.csv".<br>
                    <b>2 )</b> After selecting your file, it will ask for the identifiers, or in this case the <b>Gene.name</b> that are associated with each row of Gene Expression Data you want to compare to all other rows. <br>
                    &emsp; <b>a )</b> In this example, the user wants to analyze the Gene Expression Data within the file with the Gene Expression Data within the files that contain the following Gene.names:<br>
                    &emsp; <b>Atf4,Nck1,Artn,Ears2</b>.<br>
                    <b>3 )</b> Provide a name to save your data. In the example shown, the save is named as <b>test123</b>.<br>
                    <img src="img/AGEDS2.png" class="img" height="50%" length="50%"><br>
                    <b>4 )</b> If PHTA is unable to find the Identifiers in the file you selected, you will be returned to step 2. Otherwise, you will be transported to the <b>Access Data</b> tab, where you can access your data.
                    <h3>Analyzing with Quantitative Data</h3><hr>
                    <b>1 )</b> Select the csv file containing your data of interest. The file used in the example is "sampledata1.csv".<br>
                    <b>2 )</b> Enter the quantitative phenotypic data that you would like to compare to the other rows of Gene Expression Data. <br>
                    &emsp; <b>a )</b> PHTA will provide a template as to how the data should be formatted.<br>
                    &emsp; <b>b )</b> The data can be either comma delimited or tab delimited and each rows must be inserted in the next line. An example is shown below.<br>
                    &emsp; <b>c )</b> In this example, the user wants to compare the Gene Expression Data in the file with the following Gene Expression not found in the file: <b>sample1,1,2,3,4,5,6</b> and <b>sample2,1,2,3,4,5,6</b>.<br>
                    <b>3 )</b> Provide a name to save your data. In the example shown, the save is named as <b>test1234</b>.<br>
                    <img src="img/AQDS2.png" class="img" height="50%" length="50%"><br>
                    <b>4 )</b> If PHTA is unable to find the Identifiers in the file you selected, you will be returned to step 2. Otherwise, you will be transported to the <b>Access Data</b> tab, where you can access your data.
                    <h3>Accessing your data</h3><hr>
                    <b>1 )</b> Choose the zip file that matches the name you saved it as and select <b>View Data</b>.<br>
                    <b>2 )</b> This will make a new menu appear labelled as <b>View Plots</b>. By the same logic as before, you can choose which graph to view by selecting it and pressing the <b>Display Plot</b> button.<br>
                    <b>3 )</b> Below the View Plots Menu, there will be a  link. Selecting the link the link will download a zip file containing all your graphs and data.<br>
                    <img src="img/VP.png" class="img" height="50%" lenght="50%">';
                }
            ?>
        </div>
    </body>
</html>