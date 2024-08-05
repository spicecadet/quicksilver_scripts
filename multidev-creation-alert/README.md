# Multidev Creation Alert Quicksilver Script

Quicksilver allows automation of websites throughout the WebOps deploy process on Pantheon. This script will automate messaging during the creation of Multidev environments. On creation, an email will be created and sent via Sendgrid. A ZenDesk ticket will then be created via API. 

## Installation

This directory should be copied to your site's /private/scripts/quicksilver directory. Credentials that are required for this script to work can be kept in a secrets.json file which will be stored in your site's private directory. Copy the secrets-example.json file to secrets.json and add values for the script parameters.

The instructions show how to upload your secrets.json file to your site's private directory.
```bash
# Get SFTP credentials for your site and connect. Replace site.env in the command below with your site's information and then run the command result on output.
terminus connection:info  --field=sftp_command site.env

# SFTP secrets.json to your site.env.
cd /files/private/
put secrets.json
exit
```
In order for the script to be triggered when a Multidev environment is created, it needs to be called from your sites pantheon.yml file. The code below will need to be added to the workflows section.
```
# pantheon.yml

api_version: 1

workflows:
  create_cloud_development_environment:
    after:
      - type: webphp
        description: Send email alert when a multidev environment is created
        script: private/scripts/quicksilver/multidev-creation-alert/alert.php
```
