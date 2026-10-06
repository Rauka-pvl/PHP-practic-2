Listen 127.0.0.1:8088
Define COURSE_PUBLIC "C:/WebLabs/pr02/public"
<VirtualHost 127.0.0.1:8088>
    DocumentRoot "${COURSE_PUBLIC}"
    <Directory "${COURSE_PUBLIC}">
        Options -Indexes
        AllowOverride All
        Require local
        DirectoryIndex index.php
    </Directory>
</VirtualHost>
