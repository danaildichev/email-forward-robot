# email-forward-robot

A PHP script to check an email inbox and forward messages found.  

The file forwardEmails.php is meant to be used as a cron job. Inside the logs directory you can see an example of the output.

You will need to fill in a few details at the top of the forwardEmails.php file. I've included some comments to help you with that. The purpose of this script is to forward emails from a generic inbox to an admin in your own company.

## initialize the inbox reader

Someone else created the EmailReader class. I've seen a couple other people use it. I put together the forwarding script that uses the class.

the email reader is initialized with a mail server name, username, password, and port number.

// login
$server = '';       // 'imap-mail.outlook.com'
$user = '';         // 'username@outlook.com'
$pass = '';         // your password
$port = 993;

$emailReader = new EmailReader($server, $user, $pass, $port);

### get message count

$numberOfMessages = $emailReader->getMessageCount();

returns 0 or more results.

### get a message by index

the following loop will get all messages and store them in an array.

$messages = [];
for ($i = 0; $i < $emailReader->getMessageCount(); $i++) {
    array_push($messages, $emailReader->get($i));
}

The forwardEmails.php script will then send all the emails that it finds to an admin. It first checks the name of the sender. If the name of the sender does not contain your own brand name it will forward the message.

I was going to extend the class for a function called moveNoReply() but I haven't gotten to it. 

Then the script closes the connection to the inbox.

to set up a cron job to run this script at midnight each night, make sure you give this file permissions and then go to your crontab and add the following

0 0 * * * /path/to/forwardEmails.php >> /path/to/logs/forwardingLog
