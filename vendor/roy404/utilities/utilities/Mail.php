<?php
	/**
	 * Class Mail
	 *
	 * A fluent and configurable mail sending utility that wraps PHPMailer for simplicity.
	 * Allows setting recipients, sender, subject, body, headers, attachments, and embedded images.
	 * Supports SMTP configuration and error handling.
	 *
	 * Example Usage:
	 *
	 * ```php
	 * use App\Utilities\Mail;
	 *
	 * // Configure the mailer
	 * Mail::configure('smtp.mailtrap.io', 2525, [
	 *     'username' => 'your-username',
	 *     'password' => 'your-password',
	 * ]);
	 *
	 * // Send a message
	 * Mail::to('recipient@example.com')
	 *     ->from('sender@example.com')
	 *     ->subject('Test Email')
	 *     ->body('<p>This is a test email.</p>')
	 *     ->cc('cc@example.com')
	 *     ->bcc('bcc@example.com')
	 *     ->replyTo('noreply@example.com', 'No Reply')
	 *     ->header('X-Custom-Header', 'value')
	 *     ->attach('file-content', 'file.txt')
	 *     ->embedImage('/path/to/image.jpg', 'cid001')
	 *     ->send();
	 * ```
	 *
	 * Static Methods:
	 * @method static bool configure(string $host, int $port = 1025, array $credentials = []) Set up SMTP configuration.
	 * @method static Mail to(string|array $emails) Set recipient(s).
	 * @method static bool mail(object $className) Call `send()` method of an object.
	 *
	 * Instance Methods:
	 * @method Mail from(string $email) Set the "from" email address.
	 * @method Mail subject(string $subject) Set the subject of the email.
	 * @method Mail body(string $body) Set the body content of the email.
	 * @method Mail header(string $key, string $value) Add a custom email header.
	 * @method Mail charset(string $charset) Set the character set (default: UTF-8).
	 * @method Mail contentType(string $contentType) Set the content type (default: text/html).
	 * @method Mail cc(string $email) Add a CC recipient.
	 * @method Mail bcc(string $email) Add a BCC recipient.
	 * @method Mail replyTo(string $email, string $name = '') Set a reply-to address.
	 * @method Mail attach(string $content, string $filename, array $opt = []) Attach a file with optional MIME type and encoding.
	 * @method Mail embedImage(string $path, string $cid) Embed an image in the message body.
	 * @method bool send() Send the email using the configured settings.
	 *
	 * @package App\Utilities
	 */

	namespace App\Utilities;

	use Exception;
	use PHPMailer\PHPMailer\PHPMailer;

	/**
	 * Class Mail
	 *
	 * A utility class for sending emails using PHPMailer.
	 */
	final class Mail
	{
		/**
		 * Mail configuration settings (host, port, credentials).
		 * @var array
		 */
		private static array $configure = [];
		private array $temp_config;

		/**
		 * Instance of PHPMailer for sending emails.
		 * @var PHPMailer|null
		 */
		protected static ?PHPMailer $mailer = null;

		/**
		 * Email character set.
		 * @var string
		 */
		protected string $charset = 'UTF-8';

		/**
		 * Email content type.
		 * @var string
		 */
		protected string $contentType = 'text/html';

		/**
		 * Sender email address.
		 * @var string
		 */
		protected string $from = 'no-reply@example.com';

		/**
		 * Recipient email addresses.
		 * @var array
		 */
		protected array $recipients;

		/**
		 * Email subject.
		 * @var string
		 */
		protected string $subject = '';

		/**
		 * Email body content.
		 * @var string
		 */
		protected string $body = '';

		/**
		 * Custom email headers.
		 * @var array
		 */
		protected array $headers = [];

		/**
		 * Attachments to be added to the email.
		 * @var array
		 */
		protected array $attachments = [];

		/**
		 * Embedded images in the email body.
		 * @var array
		 */
		protected array $embeddedImages = [];

		/**
		 * Reply-to addresses.
		 * @var array
		 */
		protected array $replyTo = [];

		/**
		 * CC email addresses.
		 * @var array
		 */
		protected array $cc = [];

		/**
		 * BCC email addresses.
		 * @var array
		 */
		protected array $bcc = [];

		/**
		 * Configure SMTP settings.
		 *
		 * @param array $config
		 */
		private function __construct(array $config = []) {
			$this->temp_config = $config;
		}

		/**
		 * Configure SMTP settings.
		 *
		 * @param string $host
		 * @param int $port
		 * @param string $encryption
		 * @param string $smtp
		 * @param array $credentials ['username' => '', 'password' => '']
		 * @return bool
		 */
		public static function configure(string $host, int $port = 1025, string $encryption = 'tls', string $smtp = 'smtp', array $credentials = []): bool
		{
			if (!$host || !$port)
				return false;

			self::$configure = [
				'host' => $host,
				'port' => $port,
				'encryption' => $encryption,
				'mailer' => $smtp
			];

			if ($credentials) {
				self::$configure['credentials'] = $credentials;
			}

			return true;
		}

		/**
		 * Manual configuration for mailing.
		 *
		 * @param array $config
		 * @return self
		 */
		public static function driver(array $config): self {
			return new self($config);
		}

		/**
		 * Set recipient email(s).
		 *
		 * @param string|array $emails
		 * @return self
		 */
		public static function to(string|array $emails): self
		{
			$instance = new self();
			if (is_string($emails))
				$emails = [$emails];

			$instance->recipients = array_filter($emails, fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL));
			return $instance;
		}

		/**
		 * Send an email using a class instance that has a `send()` method.
		 *
		 * @param object $className
		 * @return bool
		 */
		public static function mail(object $className): bool
		{
			$obj = $className;
			if (method_exists($obj, 'send'))
				return $obj->send();

			return false;
		}

		/**
		 * Set the sender's email address.
		 *
		 * @param string $email
		 * @return self
		 */
		public function from(string $email): self
		{
			if ($email)
				$this->from = $email;

			return $this;
		}

		/**
		 * Set the email subject.
		 *
		 * @param string $subject
		 * @return self
		 */
		public function subject(string $subject): self
		{
			if ($subject)
				$this->subject = $subject;

			return $this;
		}

		/**
		 * Set the email body.
		 *
		 * @param string $body
		 * @return self
		 */
		public function body(string $body): self
		{
			if ($body)
				$this->body = $body;

			return $this;
		}

		/**
		 * Add a custom header to the email.
		 *
		 * @param string $key
		 * @param string $value
		 * @return self
		 */
		public function header(string $key, string $value): self
		{
			$this->headers[] = "$key: $value";
			return $this;
		}

		/**
		 * Set the character set for the email.
		 *
		 * @param string $charset
		 * @return self
		 */
		public function charset(string $charset): self
		{
			if ($charset)
				$this->charset = $charset;

			return $this;
		}

		/**
		 * Set the content type for the email.
		 *
		 * @param string $contentType
		 * @return self
		 */
		public function contentType(string $contentType): self
		{
			if ($contentType)
				$this->contentType = $contentType;

			return $this;
		}

		/**
		 * Add a CC recipient.
		 *
		 * @param string $email
		 * @return self
		 */
		public function cc(string $email): self
		{
			if ($email)
				$this->cc[] = $email;

			return $this;
		}

		/**
		 * Add a BCC recipient.
		 *
		 * @param string $email
		 * @return self
		 */
		public function bcc(string $email): self
		{
			if ($email)
				$this->bcc[] = $email;

			return $this;
		}

		/**
		 * Add a reply-to address.
		 *
		 * @param string $email
		 * @param string $name
		 * @return self
		 */
		public function replyTo(string $email, string $name = ''): self
		{
			if ($email)
				$this->replyTo[] = compact('email', 'name');

			return $this;
		}

		/**
		 * Attach a file to the email.
		 *
		 * @param string $content The raw content of the file.
		 * @param string $filename
		 * @param array $opt ['encoding' => 'base64', 'type' => 'application/octet-stream']
		 * @return self
		 */
		public function attach(string $content, string $filename, array $opt = []): self
		{
			if ($content && $filename) {
				$this->attachments[] = [
					'content' => $content,
					'filename' => $filename,
					'opt' => $opt
				];
			}

			return $this;
		}

		/**
		 * Embed an image into the email body.
		 *
		 * @param string $path
		 * @param string $cid
		 * @return self
		 */
		public function embedImage(string $path, string $cid): self
		{
			if ($path && $cid) {
				$this->embeddedImages[] = [
					'path' => $path,
					'cid' => $cid
				];
			}

			return $this;
		}

		/**
		 * Send the email.
		 *
		 * @return bool
		 * @throws Exception
		 */
		public function send(): bool
		{
			$conf = self::$configure;
			if (!empty($this->temp_config)) {
				$conf = $this->temp_config;
			}

			if ($conf) {
				try {
					$mailerType = $conf['mailer'] ?? 'smtp';
					$mail = new PHPMailer(true);

					switch ($mailerType) {
						case 'smtp':
							$mail->isSMTP();
							$mail->Host = $conf['host'] ?? 'localhost';
							$mail->Port = $conf['port'] ?? 587;
							$mail->SMTPSecure = $conf['encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;
							$mail->SMTPAuth = !empty($conf['credentials']);

							if (!empty($conf['credentials'])) {
								$mail->Username = $conf['credentials']['username'] ?? '';
								$mail->Password = $conf['credentials']['password'] ?? '';
							}

							// Optional: Disable auto TLS if you explicitly control encryption
							$mail->SMTPAutoTLS = false;
							break;

						case 'sendmail':
							$mail->isSendmail();
							break;

						case 'mail':
							$mail->isMail();
							break;

						case 'none':
						default:
							break;
					}

					self::$mailer = $mail;
				} catch (Exception $e) {
					throw new Exception('Mailer Error: ' . $e->getMessage());
				}
			}

			if (!self::$mailer)
				throw new Exception('Mailer is not configured.');

			try {
				$mail = clone self::$mailer;
				$mail->CharSet = $this->charset;
				$mail->isHTML($this->contentType === 'text/html');

				$mail->setFrom($this->from);
				foreach ($this->recipients as $email) {
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$mail->addAddress($email);
					}
				}

				$mail->Subject = $this->subject;
				$mail->Body = $this->body;

				foreach ($this->headers as $header) {
					if (strpos($header, ':') !== false) {
						[$key, $value] = explode(':', $header, 2);
						$mail->addCustomHeader(trim($key), trim($value));
					}
				}

				foreach ($this->attachments as $file) {
					$mail->addStringAttachment(
						$file['content'],
						$file['filename'],
						$file['opt']['encoding'] ?? 'base64',
						$file['opt']['type'] ?? 'application/octet-stream'
					);
				}

				foreach ($this->embeddedImages as $image)
					$mail->addEmbeddedImage($image['path'], $image['cid']);

				foreach ($this->cc as $email)
					$mail->addCC($email);

				foreach ($this->bcc as $email)
					$mail->addBCC($email);

				foreach ($this->replyTo ?? [] as $reply)
					$mail->addReplyTo($reply['email'], $reply['name']);

				$mail->send();
				return true;
			} catch (Exception $e) {
				throw new Exception('Mailer Error: ' . $e->getMessage());
			}
		}
	}