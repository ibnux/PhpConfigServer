# PHP CONFIG SERVER

when i am using microservices architecture, i am confuse how to update configuration all services at once, then i create this script to manage all configuration.

configuration will be saved at **config** folder, you can change it to another folder.

Apache/NGINX with public IP access index.php

but private IP access config folder.

for authentication, i use IMAP to company email server. you can specify which email can access this Admin.

Do you have another solution for managing microservices configuration?

# USING IT

i am using it with docker, everytime docker started, it will download configuration. when i need to change configuration, just restart all services.

# Requirements
- PHP 7 or above
- php-imap for authentication
- chmod **config** folder to write access

# LICENSE
## Apache License 2.0

Permissions

    ✓ Commercial use
    ✓ Distribution
    ✓ Modification
    ✓ Patent use
    ✓ Private use

Conditions

    License and copyright notice 
    State changes

Limitations

    No Liability
    No Trademark use
    No Warranty

you can find license file inside folder