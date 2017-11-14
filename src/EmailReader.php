<?php

class EmailReader
{

    // imap server connection
    public $conn;

    // inbox storage and inbox message count
    private $inbox;
    private $msg_cnt;

    // email login credentials
    private $server;
    private $user;
    private $pass;
    private $port;

    //
    // ----------------------------------------------------------------
    //

    // ----------------------------------------------
    // connect to the server and get the inbox emails
    function __construct($server, $user, $pass, $port)
    {
        $this->server = $server;
        $this->user = $user;
        $this->pass = $pass;
        $this->port = $port;
        $this->connect();
        $this->inbox();
    }

    // ----------------------------------------------
    // open the server connection
    // the imap_open function parameters will need to be changed for the particular server
    // these are laid out to connect to a Dreamhost IMAP server
    function connect()
    {
        $this->conn = imap_open('{' . $this->server . ':' . $this->port . '/imap/ssl/novalidate-cert}', $this->user, $this->pass, NULL, 1, array('DISABLE_AUTHENTICATOR' => 'GSSAPI'));
    }

    // ----------------------------------------------
    // read the inbox
    function inbox()
    {
        $this->msg_cnt = imap_num_msg($this->conn);

        $in = array();
        for ($i = 1; $i <= $this->msg_cnt; $i++) {
            $in[] = array(
                'index' => $i,
                'header' => imap_headerinfo($this->conn, $i),
                'body' => imap_body($this->conn, $i),
                'structure' => imap_fetchstructure($this->conn, $i)
            );
        }

        $this->inbox = $in;
    }

    // ----------------------------------------------
    // close the server connection
    function close()
    {
        $this->inbox = array();
        $this->msg_cnt = 0;

        imap_close($this->conn);
    }


    // ----------------------------------------------
    // get a message by index
    function get($msg_index = NULL)
    {
        if (count($this->inbox) <= 0) {
            return array();
        } elseif (!is_null($msg_index) && isset($this->inbox[$msg_index])) {
            return $this->inbox[$msg_index];
        }

        return $this->inbox[0];
    }


    // ----------------------------------------------
    // get message count
    public function getMessageCount()
    {
        return $this->msg_cnt;
    }


    // ----------------------------------------------
    // get message body
    public function fetchBody($id)
    {
        return imap_fetchbody($this->conn, $id, '1');
    }


    // ----------------------------------------------
    // move message to 'processed' folder
    function move($msg_index, $folder = 'Forwarded')
    {
        // move on server
        imap_mail_move($this->conn, $msg_index, $folder);
        imap_expunge($this->conn);

        // re-read the inbox
        $this->inbox();
    }


}

?>