# WikiRumours

WikiRumours is a web- and mobile-based platform for moderating
misinformation and disinformation. The software is free and open
source under an MIT license, which means that it can be used for
open, commerical or proprietary use, without mandatory
attribution.

WikiRumours is the brainchild of The Sentinel Project:
http://thesentinelproject.org

# Setup and installation

The following steps are required to install and start using an
instance of WikiRumours.

- Create a database and import the scheme provided in the
  db_setup folder. Consider using a unique prefix for your
  table names.

- Update the files in the source/config folder.

- Copy the files over to your web server and point the root of
  your virtual domain at the folder source/web_root.
  
- Make sure the .htaccess file sits in source/web_root (a backup
  of the file has been provided with a TXT extension since
  Windows sometimes hides files with solely a file extension)
  
- Set up a cron job and point it at source/cron/cron.php.

- Set up an email address to use with the software. This isn't
  strictly necessary, but outbound emails with the same domain
  are less likely to be intercepted by spam filters.

- Go to the new site through a browser and register. The first
  user to register automatically becomes an administrator.

# Customization

WikiRumours is built on the open source Tidal Lock PHP framework,
so before customizing it's recommended that you first understand
how the framework functions.

# Questions?

Contact us at wikirumours.org or thesentinelproject.org
