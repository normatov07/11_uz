<?php defined('SYSPATH') or die('No direct script access.');

class email extends email_Core{

	/**
	 * Send an email message.
	 *
	 * @param   string|array  recipient email (and name), or an array of To, Cc, Bcc names
	 * @param   string|array  sender email (and name)
	 * @param   string        message subject
	 * @param   string        message body
	 * @param   boolean       send email as HTML
     * @param   array         attached files
	 * @return  integer       number of emails sent
	 */
	public static function send($to, $from, $subject, $message, $html = FALSE, $attachments = array())
	{
        // Connect to SwiftMailer
		(email::$mail === NULL) and email::connect();

		// Determine the message type
		$html = ($html === TRUE) ? 'text/html' : 'text/plain';

		// Create the message
		$message = new Swift_Message($subject, $message, $html, '8bit', 'utf-8');

		if (is_string($to))
		{
			// Single recipient
			$recipients = new Swift_Address($to);
		}
		elseif (is_array($to))
		{
			if (isset($to[0]) AND isset($to[1]))
			{
				// Create To: address set
				$to = array('to' => $to);
			}

			// Create a list of recipients
			$recipients = new Swift_RecipientList;

			foreach ($to as $method => $set)
			{
				if ( ! in_array($method, array('to', 'cc', 'bcc')))
				{
					// Use To: by default
					$method = 'to';
				}

				// Create method name
				$method = 'add'.ucfirst($method);

				if (is_array($set))
				{
					// Add a recipient with name
					$recipients->$method($set[0], $set[1]);
				}
				else
				{
					// Add a recipient without name
					$recipients->$method($set);
				}
			}
		}

		if (is_string($from))
		{
			// From without a name
			$from = new Swift_Address($from);
		}
		elseif (is_array($from))
		{
			// From with a name
			$from = new Swift_Address($from[0], $from[1]);
		}

        if (!empty($attachments))
        {
            for ($i = 0, $cnt = count($attachments); $i < $cnt; $i++)
            {
                $swift_file = new Swift_File($attachments[$i]['path']);
                $swift_attachment = new Swift_Message_Attachment;
                $swift_attachment->setData($swift_file);
                $swift_attachment->setFileName($attachments[$i]['name']);
                $message->attach($swift_attachment);
            }
        }

		//var_dump($message);
		//var_dump($recipients);
		//var_dump($from);
		//exit();

		$sended = email::$mail->send($message, $recipients, $from);

        return $sended;
	}

}
/*?>*/
