# MREFD ref-dash

This is the dashboard as seen on [M17-M17 Reflector](https://ref.m17.link) to be used with mrefd.

### Clone dashboard to /var/www

```bash
sudo rm /var/www/html
sudo git clone https://github.com/m17-project/ref-dash /var/www/html     # or where ever your system www root is located
```

Please note that your www root directory might be some place else. There is one file that needs configuration. Edit the copied files, not the ones from the repository:

```bash
cd /var/www/html/include
sudo cp config.inc.php.dist config.inc.php
```

- **include/config.inc.php** - At a minimum set your email address, country and comment. **Do not** enable the calling home feature. This feature is not appropriate for mrefd.