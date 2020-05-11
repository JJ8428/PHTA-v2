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


    # Upload function
    if (isset($_POST['upload']))
    {
        $target_dir = 'users/dirs/' . $_SESSION['whoami'] . '/files/' . $_FILES['fileToUpload']['name'];
        move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_dir);
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
        $temp = explode(',', fgets($read));
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
        }
        $_SESSION['step2'] = 0;
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
        $_2dArray = explode(',', $_POST['userInput2']);
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
                if ($tmp == strtolower($_2dArray[$x]))
                {
                    $count -= 1;
                    array_push($_SESSION['array'], explode(',', $line)); 
                }
            }
        }
        fclose($read);
        if ($count != 0)
        {
            $CalcMessage2 = 'The given data was not found in the file';
        }
        else
        {
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
            fclose($write);
            $write2 = fopen('activeDir', 'w');
            fwrite($write2, $_SESSION['whoami']);
            fclose($write2);
            $_SESSION['step1'] = 0;
            // Run the Python script from a virtual env
            // Reminder: Ask Mrs. Casey to create the virtual env with python and modules through email
            echo shell_exec('env/bin/python src/Pearson.py 2>&1');
        }
        $_SESSION['step2'] = 0;   
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
        $_SESSION['pngFile'] = '<img src="' . $_POST['pngfile'] . '"><br><br>';
    }

    # Delete Commit Data
    if (isset($_POST['deleteCommit']))
    {
        unlink($_POST['deletezipfile']);
    }
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>PHTA-v2</title>
    </head>
    <style>
        body
        {
            background-color: black;
        }
        #body
        {
            background-color: white;
        }
        h3, b, hr
        {
            color: #73000a;
        }
    </style>
    <body>
        <div id="body">
            <h1 style="display:inline; font-weight:normal">
                <b>PH</b>enotype <b>T</b>ranscriptomic <b>A</b>ssociation <b>Calculator</b>
            </h1>
            <img id="img2" src="img/nsf-logo.png" height="68px" width="68px" style="display: inline; float:right">
            <img src="img/usc.jpeg" height="68px" width="68px" style="display: inline; float:right">
            </br>
            Work of Dr. Homayoun Valafar, Dr. Hippokratis Kiaris, Naga Venkata Sai Jagjit (JJ) Satti, Youwen Zhang
            <hr>
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
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    // Logout menu
                    if ($_SESSION['logged'])
                    {
                        echo '<b>Profile:</b> ' . $_SESSION['whoami'] .
                        ' <input type="submit" name="logout" value="Logout">' . 
                        ' <b>Navigate:</b> <input type="submit" name="page1" value="Files">' . 
                        ' <input type="submit" name="page2" value="Commit">' . 
                        ' <input type="submit" name="page3" value="View">';
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
                if ($_SESSION['logged']  && $_SESSION['page'] == 2)
                {
                    echo '<h3>Commit with Hypothetical Phenotypes:</h3><hr>';
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
                        '<input type="submit" name="commit" value="Commit">';
                    }
                ?>
            </form>
            <?php
                if ($_SESSION['logged'] && $_SESSION['page'] == 2)
                {
                    echo '<h3>Commit with Pre-existing Phenotypes:</h3><hr>';
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
                        '<input type="submit" name="commit2" value="Commit">';
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
                        echo '<h3>Access Commits:</h3><hr>' .
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
                        echo '<h3>Delete Commits:</h3><hr>' .
                        'Select the save to delete:<br><br>' .
                        '<select name="deletezipfile">';
                        $files = scandir('users/dirs/' . $_SESSION['whoami'] . '/zip');
                        for ($x = 2; $x < sizeof($files); $x++)
                        {
                            echo '<option value="' .  'users/dirs/' . $_SESSION['whoami'] . '/zip/' . $files[$x] . '">' . $files[$x] . '</option>';
                        }
                        echo '</select><br><br>' . 
                        '<input type="submit" name="deleteCommit" value="Delete Commit">';
                    }
                ?>
            </form>
        </div>
    </body>
</html>