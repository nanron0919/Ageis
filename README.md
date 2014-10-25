# Ageis
##  Introduce 
*   Easy to use
*   Isolated environment
*   Nice configuation

##  Quick start
1.   Download this framework
2.   Setup your nginx config below

        location / {
          try_files $uri $uri/ /index.php?$args;
        }

        location ~ \.php {
          fastcgi_pass   127.0.0.1:9000;
          fastcgi_index  index.php;
          fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
          include        fastcgi_params;
        }
3. Enjoy it      
