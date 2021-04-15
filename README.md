# PPI
Prüfungsprotokolle Interface of Fachschaft Informatik Tübingen.

## Installation with Docker
For deploying PPI with Docker, PHP, Postgres and nginx, configuration files can be found [here](https://github.com/fsi-tue/docker/tree/master/ppi).
At the moment only Postgres is supported.


## PostgreSQL Database

### Installation (Debian)
https://www.howtoforge.de/anleitung/wie-man-postgresql-und-phppgadmin-auf-ubuntu-1804-lts-installiert/
```
# Create the file repository configuration:
sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'

# Import the repository signing key:
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -

# Update the package lists:
sudo apt-get update

# Install the latest version of PostgreSQL.
# If you want a specific version, use 'postgresql-12' or similar instead of 'postgresql':
sudo apt-get -y install postgresql postgresql-contrib phppgadmin
sudo su postgres
psql
\password postgres
then type the new database password twice
\q
psql
CREATE DATABASE postgresunittests;
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
