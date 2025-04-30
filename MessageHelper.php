<?php

/**
 * MessageHelper
 *
 * A utility class for managing email, SMS and push notifications
 */

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MessageHelper
{
	private $pdo;

	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	private function insertQueueMessage($type, $recipient, $subject, $message, $cc = null, $bcc = null, $priority = 'normal', $status = 'queued')
	{
		$stmt = $this->pdo->prepare("
			INSERT INTO message_queue (
				message_type, recipient, cc, bcc, subject, message, priority, queue_date, status
			) VALUES (
				:message_type, :recipient, :cc, :bcc, :subject, :message, :priority, NOW(), :status
			)
		");

		$stmt->execute([
			':message_type' => $type,
			':recipient' => $recipient,
			':cc' => $cc,
			':bcc' => $bcc,
			':subject' => $subject,
			':message' => $message,
			':priority' => $priority,
			':status' => $status,
		]);

		return $this->pdo->lastInsertId();
	}

	public function enqueueMessage($type, $recipient, $subject, $message, $cc = null, $bcc = null, $priority = 'normal')
	{
		if ($priority === 'high') {
			// Directly send high-priority messages
			$result = $this->sendEmail($recipient, $subject, $message, strip_tags($message), $cc, $bcc);

			// Log the message in the queue for tracking purposes
			$messageId = $this->insertQueueMessage($type, $recipient, $subject, $message, $cc, $bcc, 'high', $result['success'] ? 'sent' : 'failed');

			if (!$result['success']) {
				throw new Exception("Failed to send high-priority message: " . $result['error']);
			}

			$status = $result['success'] ? 'sent' : 'failed';
			$result = $result['success'] ? 'Message successfully sent.' : 'Failed to send message.';
			$this->updateMessageStatus($messageId, $status, $result);

			return $result;
		} else {
			// Enqueue the message for normal processing
			return $this->insertQueueMessage($type, $recipient, $subject, $message, $cc, $bcc, $priority);
		}
	}
	
	public function processTemplate($template, $placeholders, $convertNewlinesToBr = true)
	{
		// Replace placeholders
		foreach ($placeholders as $key => $value) {
			$template = str_replace("{{ $key }}", $value, $template);
		}

		// Convert newlines to <br> if requested
		if ($convertNewlinesToBr) {
			$template = nl2br($template, false); // Preserve explicit markup
		}

		return $template;
	}	

	public function updateMessageStatus($messageId, $status, $result)
	{
		$stmt = $this->pdo->prepare("
			UPDATE message_queue
			SET status = :status,
				sent_date = NOW(),
				result = :result
			WHERE id = :id
		");

		$stmt->execute([
			':status' => $status,
			':result' => $result,
			':id' => $messageId
		]);
	}


	public function processMessages($batchSize)
	{
		$stmt = $this->pdo->prepare("
			SELECT * FROM message_queue
			WHERE status = 'queued'
			ORDER BY priority DESC, queue_date ASC
			LIMIT :batchSize
		");
		$stmt->bindValue(':batchSize', $batchSize, PDO::PARAM_INT);
		$stmt->execute();

		$messages = $stmt->fetchAll();

		foreach ($messages as $message) {
			switch ($message['message_type']) {
				case 'email':
					// Send email
					$result = $this->sendEmail(
						$message['recipient'], 
						$message['subject'], 
						$message['message'],  // Assuming this contains the HTML body
						strip_tags($message['message']), // Plain text fallback
						$message['cc'], 
						$message['bcc']
					);

					// Update message status
					$status = $result['success'] ? 'sent' : 'failed';
					$this->updateMessageStatus($message['id'], $status, $result['success'] ? 'Email sent successfully' : $result['error']);
					break;

				case 'sms':
					// Placeholder for SMS processing
					$this->updateMessageStatus($message['id'], 'skipped', 'SMS processing not yet implemented');
					break;

				case 'push':
					// Placeholder for push notification processing
					$this->updateMessageStatus($message['id'], 'skipped', 'Push notification processing not yet implemented');
					break;

				default:
					// Unknown message type
					$this->updateMessageStatus($message['id'], 'failed', 'Unknown message type');
					break;
			}
		}
	}
		
	
	public function sendEmail($recipient, $subject, $htmlMessage, $plainMessage = '', $cc = null, $bcc = null)
	{
		// Load SMTP settings from .env
		$dsn = sprintf(
			'smtp://%s:%s@%s:%s',
			getenv('SMTP_USER'),
			getenv('SMTP_PASS'),
			getenv('SMTP_HOST'),
			getenv('SMTP_PORT') ?: 587
		);

		$transport = Transport::fromDsn($dsn);
		$mailer = new Mailer($transport);

		$email = (new Email())
			->from(new Address(getenv('SMTP_USER'), SITE_TITLE))
			->to($recipient)
			->subject($subject)
			->text($plainMessage)
			->html($htmlMessage);

		if ($cc) {
			$email->cc(explode(',', $cc));
		}

		if ($bcc) {
			$email->bcc(explode(',', $bcc));
		}

		// Send the message
		try {
			$mailer->send($email);
			return ['success' => true, 'result' => 'Email sent successfully'];
		} catch (TransportExceptionInterface $e) {
			return ['success' => false, 'error' => $e->getMessage()];
		}	
	}
	
}
