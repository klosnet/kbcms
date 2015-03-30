# Introduction #

This is a guide to install KB CMS on Ubuntu. Many of the steps may apply to other Ubuntu versions, other distributions or even other operating systems. This guide is specifically made for one certain setup for simplicity, but others may follow later.

# Ubuntu #

The initial setup can be done with the following commands in a terminal window:

```
# Install required packages
sudo apt-get -y install php5
# Download KB CMS and set it up
sudo chown -R `whoami` /var/www
mkdir /var/www/admin
wget "https://kbcms.googlecode.com/files/kbcms-0.2.4.tar.gz" -O - | tar -xzf - -C /var/www/admin
sudo chown -R `whoami`:www-data /var/www
sudo chmod -R g+w /var/www
sudo /etc/init.d/apache2 restart
```

KB CMS should now be set up on http://localhost/admin to create the resulting site on http://localhost. The default username is "admin" and the default password is "changeme".