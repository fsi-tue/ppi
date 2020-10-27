# ppi

## PostgreSQL Database

### Installation (Debian)
https://www.howtoforge.de/anleitung/wie-man-postgresql-und-phppgadmin-auf-ubuntu-1804-lts-installiert/
```
sudo apt update
sudo apt -y install postgresql postgresql-contrib phppgadmin
sudo su postgres
psql
\password postgres
then type the new database password twice
\q
exit
cd /etc/apache2/conf-available/
sudo nano phppgadmin.conf
Require local -> #Require local
add 'Require all granted' below
save that and exit
cd /etc/phppgadmin/
sudo nano config.inc.php
$conf['extra_login_security'] = true; -> $conf['extra_login_security'] = false;
save that and exit
sudo systemctl restart postgresql
sudo systemctl restart apache2
open http://localhost/phppgadmin/
log in with user: postgres an the password set above```

### Database setup
 - two databases are needed within the Postgres setup:
    - postgres
    - postgresunittests 
