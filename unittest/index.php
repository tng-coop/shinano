<html>
    <head>
        <title> index </title>
    </head>
    <body>
        <h4> contains list of the directory.</h4>
        <?php
        $dir = __DIR__;
        $files = scandir($dir);

        print("Contains of this directory ({$dir}) is below. <br />");
        print("beware the treat of '.' and '..' . <br />");

        print("<ul>");
        array_map(fn($file) => print("<li><a href='./{$file}'> {$file} </a></li>"),
                  $files);
        print("</ul>");
        ?>
    </body>
</html>

