# Oyes Booking Appointment System
<hr><p>Book Appointment and sync with google calendar also send notification emails to attendees</p><h2>Technologies Used</h2>
<hr><ul>
<li>laravel</li>
</ul><ul>
<li>vue js</li>
</ul><ul>
<li>Google oAuth</li>
</ul><ul>
<li>Google Calendar</li>
</ul><h2>Features</h2>
<hr><ul>
<li>Appointment Booking form</li>
</ul><ul>
<li>Appointment Slot Checker with current time and availability</li>
</ul><ul>
<li>Google Api Integration</li>
</ul><ul>
<li>Email Notification</li>
</ul><ul>
<li>Admin Page</li>
</ul><ul>
<li>Validation</li>


### Screenshots
<div align="center"> 
  <img src="public/screenshort/ss1.png" alt="screenshot" />
  <img src="public/screenshort/ss2.png" alt="screenshot" />
  <img src="public/screenshort/login.png" alt="screenshot" />
  <img src="public/screenshort/reg.png" alt="screenshot" />
  <img src="public/screenshort/oauth.png" alt="screenshot" />
  <img src="public/screenshort/admin.png" alt="screenshot" />
</div>

<hr><p><img src="https://github.com/0y3/Oyes-Appointment-Booking-System12/blob/master/public/screenshort/admin.png" alt=""></p><h2>Setup</h2>
<hr><p>PHP (8.1 or higher)</p>
<p>Composer (Dependency Manager)</p>
<p>Node.js (for frontend assets)</p>
<p>MySQL or another database system</p>
<p>Git (for cloning the repository)</p><h5>Steps</h5><ul>
<li>Git Clone project</li>
</ul><ul>
<li>cd project</li>
</ul><ul>
<li>composer i</li>
</ul><ul>
<li>npm i</li>
</ul><ul>
<li>composer run dev</li>
</ul><ul>
<li>copy .env.example and save it as .env</li>


### Environment Variables

To run this project, you will need to add the following environment variables to your .env file

```bash
#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

 database.default.hostname = localhost
 database.default.database = dukkaprojectdb
 database.default.username = root
 database.default.password = ''
 database.default.DBDriver = MySQLi
 database.default.DSN = 'mysql:dbname=otfonline;host=localhost'
 
 
 app.sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler'
 app.sessionCookieName = 'ci_session'
 app.sessionExpiration = 7200
 app.sessionMatchIP = true
 app.sessionTimeToUpdate = 1
 app.sessionRegenerateDestroy = true
 
 encryption.key = hex2bin:ac3b50af48230d45994c3fee0d3652a4acbc68a31c87f9c1add4219e447fdd62 
 encryption.driver = OpenSSL
 encryption.blockSize = 16
 encryption.digest = SHA512
 
 
#--------------------------------------------------------------------
# flutterwave Gateway
#--------------------------------------------------------------------
FLW_MODE = test
#YOUR Flutterwave KEYS

FLW_TEST_PUBLIC_KEY = FLWPUBK_TEST-43d25c352d75307ac1881121b2af4c23-X
FLW_TEST_ENCRYP_KEY = FLWSECK_TEST62def68454e3
 
```

<!-- Run Locally -->

### Run Locally

Go to the project directory

```
  cd my-project
```

run

```
  composer i
```
```
  npm i
```
```
  php artisan migrate
```

Start the server

```
  composer run dev
```

Run queue Woker server

```
 php artisan queue:work
```

<!-- Usage -->

## Usage

```dash
http://127.0.0.1:8000/
http://127.0.0.1:8000/register
http://localhost:8081/login

http://localhost:8081/admin
```

## Contact

<hr><p><span style="margin-right: 30px;"></span><a href="https://www.linkedin.com/in/0y3"><img target="_blank" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/linkedin/linkedin-original.svg" style="width: 10%;"></a><span style="margin-right: 30px;"></span><a href="https://github.com/0y3"><img target="_blank" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/github/github-original.svg" style="width: 10%;"></a></p>