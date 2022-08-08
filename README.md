Installation

1. install composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

2. install coop
Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
irm get.scoop.sh | iex

3. install symfony cli
scoop install symfony-cli

Create a project
php composer.phar create-project symfony/website-skeleton project_name

Add ORM to project
php composer.phar require symfony/orm-pack

Add maker bundle
php composer.phar require --dev symfony/maker-bundle

Add annotations for routing
php composer.phar require annotations

Add apache support - this creates the .htaccess file inside public folder. Xamp must be configured to point to the public folder
php composer.phar require symfony/apache-pack

Start the symfony server
php composer.phar require --dev symfony/web-server-bundle
symfony server:start

Database

Add entry in .env
DATABASE_URL="mysql://root:@127.0.0.1:3306/new_aluve_db?serverVersion=mariadb-{slq_server_version}&charset=utf8mb4"
Comment out the postgres connection string 

make sure routes.yml is empty
config/routes.yaml

create file config/routes/annotations.yaml with below content
# config/routes/annotations.yaml
controllers:
resource: ../../src/Controller/
type: annotation

kernel:
resource: ../../src/Kernel.php
type: annotation


import existing database entities
php bin/console doctrine:mapping:import --force "App\Entity" annotation --path=src/Entity_new

delete all reservations
TRUNCATE TABLE `reservation_notes`;
TRUNCATE TABLE `reservation_add_ons`;
TRUNCATE TABLE `payments`;
TRUNCATE TABLE `cleaning`;
delete FROM `reservations` where id > 0;
delete FROM `guest` where id > 0;
delete from room_images where id > 0;
delete from blocked_rooms where id > 0;
delete from ical where id > 0;
delete from schedule_messages where id > 0;
delete from rooms where id > 0;
delete from add_ons where id > 0;
delete from employee where id > 0;
delete from message_template where id > 0;
delete from schedule_messages where id > 0;