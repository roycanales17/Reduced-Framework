<?php

	namespace App\Utilities\Handler;

	use App\Utilities\Mail;

	abstract class Mailable
	{
		private string $to = '';
		private string $from = '';
		private string $subject = '';
		private string $replyTo = '';
		private string $charset = 'UTF-8';
		private string $contentType = 'text/html';
		private int $priority = 3;
		private array $cc = [];
		private array $bcc = [];
		private array $attachments = [];
		private array $embedded = [];
		private string $body = '';
		private string $plainBody = '';
		private string $altBody = '';

		protected function view(string $view, array $data = []): self
		{
			$this->body = view($view, $data);
			$this->plainBody = strip_tags(
				preg_replace(['/<br\s*\/?>/i', '/<\/p>/i'], ["\n", "\n\n"], $this->body)
			);

			return $this;
		}

		protected function text(string $view, array $data = []): self
		{
			$this->altBody = view($view, $data);
			return $this;
		}

		protected function subject(string $subject): self
		{
			$this->subject = $subject;
			return $this;
		}

		protected function from(string $from): self
		{
			$this->from = $from;
			return $this;
		}

		protected function to(string $to): self
		{
			$this->to = $to;
			return $this;
		}

		protected function cc(string $cc): self
		{
			$this->cc[] = $cc;
			return $this;
		}

		protected function bcc(string $bcc): self
		{
			$this->bcc[] = $bcc;
			return $this;
		}

		protected function replyTo(string $replyTo): self
		{
			$this->replyTo = $replyTo;
			return $this;
		}

		protected function attach(string $content, string $filename, array $opt = []): self
		{
			$this->attachments[] = compact('content', 'filename', 'opt');
			return $this;
		}

		protected function embed(string $imagePath): self
		{
			$this->embedded[] = $imagePath;
			return $this;
		}

		protected function priority(int $priority): self
		{
			$this->priority = $priority;
			return $this;
		}

		protected function charset(string $charset): self
		{
			$this->charset = $charset;
			return $this;
		}

		protected function contentType(string $contentType): self
		{
			$this->contentType = $contentType;
			return $this;
		}

		protected function build(): bool
		{
			$mail = Mail::to($this->to);

			$mail->charset($this->charset);
			$mail->contentType($this->contentType);
			$mail->subject($this->subject);
			$mail->body($this->body);
			$mail->from($this->from);

			if (!empty($this->altBody) && method_exists($mail, 'altBody')) {
				$mail->altBody($this->altBody);
			}

			if ($this->replyTo)
				$mail->header('Reply-To', $this->replyTo);

			if ($this->priority)
				$mail->header('X-Priority', (string) $this->priority);

			foreach ($this->cc as $email)
				$mail->cc($email);

			foreach ($this->bcc as $email)
				$mail->bcc($email);

			foreach ($this->embedded as $image) {
				if (isset($image['cid'], $image['path'])) {
					$mail->embedImage($image['path'], $image['cid']);
				}
			}

			if ($this->attachments) {
				foreach ($this->attachments as $attachment) {
					if (isset($attachment['content'], $attachment['filename'])) {
						$mail->attach(
							$attachment['content'],
							$attachment['filename'],
							$attachment['opt'] ?? []
						);
					}
				}
			}

			$mail->body($this->body ?: $this->plainBody);
			return $mail->send();
		}

		abstract public function send(): bool;
	}
