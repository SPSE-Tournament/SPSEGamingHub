SPSEGamingHub

#### PHP Based web application made specifically for SPSE Lan Gaming Tournament. @Roudnas

#### Has since been made into a graduation project.

## Docker usage

### SPSEGamingHub

#### Production image

```
docker run -d --name SPSEGamingHub -p 127.0.0.1:8080:80 -v /path/to/.env:/var/www/.env ghcr.io/spse-tournament/spsegaminghub:master
```

#### Staging image

```
docker run -d --name SPSEGamingHub -p 127.0.0.1:8080:80 -v /path/to/.env:/var/www/.env ghcr.io/spse-tournament/spsegaminghub:staging
```

### Watchtower (optional, for automatic updates)

```
docker run -d --name watchtower -v /var/run/docker.sock:/var/run/docker.sock --env TZ=Europe/Prague containrrr/watchtower --interval 3600 --cleanup --rolling-restart
```

## Stack

#### `PHP` `MYSQL` `ES6 JS` `PRIVATE REST API`

## File Structure

#### Following a pretty classic MVC philosophy, the base file structure is as follows:

- #### Controllers
  - ##### Connecting models with views and in this case also routing `GET` requests
- #### Models
  - ##### Logical structures
- #### Views
  - ##### custom phtml files used for rendering with template php syntax
- #### Public
  - ##### publicly accessible assets, this includes all css, js, imagery and pdfs

## .env

#### Used to configure the app and provide necessary auth tokens

.env.example

    DB_HOST=db.myhost.com
    DB_USER=dbuser
    DB_PW=dbpassword
    DB_DBNAME=spsegaminghub
    MAIL_SMTP_HOST=mymailserver.com
    MAIL_USERNAME=mailusername
    MAIL_PW=mailpassword
    ADMIN_IP=11.12.13.14
    BASE=mydomain.com
    DISCORD_CID=
    DISCORD_TOKEN=
