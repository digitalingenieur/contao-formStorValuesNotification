Form Submit Notification 
==
Provides an option to send a notification mail to a specified address if a form on the frontend was submitted and the values gets stored into the database for Contao Open Source CMS.

Scenario:
If a user submits sensitive information in the Contao frontend the information should not be send via email to an address, as email is mostly unsecure. 
Instead of sending the raw data you just save the information into the database and send an notification email with a link pointing to the just created database entry.
