
1. git clone https://github.com/heheunited/php-cli.git
2. cd php-cli/
3. docker-compose up -d --build   #(nginx 10090 -> 80 port)
5. docker-compose exec php-fpm bash    #(php container)
6. composer install
7. php index.php au-gov word . For example: php index.php au-gov zxc
