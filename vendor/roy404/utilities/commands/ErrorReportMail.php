<?php

	namespace Commands;
	
	use App\Console\Command;
	
	class ErrorReportMail extends Command {
		
		protected string $signature = 'make:email_report';
		protected string $description = 'Creates an exception email reporting template.';
		
		public function handle(): void
		{
			$className = 'ErrorReportMail';
			$basePath = base_path("/mails");
			$content = <<<PHP
			<?php
				namespace Mails;
			
				use App\Utilities\Handler\Mailable;
				use Exception;
				use Error;
			
				class ErrorReportMail extends Mailable {
			
					private Error|Exception \$exception;
					private string \$to;
			
					public function __construct(Error|Exception \$exception) {
						\$this->to = config('ERROR_MAIL_RECEIVER', 'receiver@example.com');
						\$this->exception = \$exception;
					}
			
					public function send(): bool {
						\$appName = config('APP_NAME', 'Framework');
						\$subject = "🚨 [{\$appName}] Exception Detected";
						\$from = config('MAIL_FROM_ADDRESS', 'support@example.com');
			
						return \$this->view('email_report', ['exception' => \$this->exception])
							->contentType('text/html')
							->subject(\$subject)
							->from(\$from)
							->to(\$this->to)
							->build();
					}
				}
			PHP;

			$source = realpath(__DIR__ . '/../resources/blades/email_report.blade.php');
			$destination_dir = base_path('views');
			$destination = $destination_dir . '/email_report.blade.php';

			if (!is_dir($destination_dir)) {
				mkdir($destination_dir, 0755, true);
			}

			if ($source && file_exists($source)) {
				if (copy($source, $destination)) {
					$this->success("✅ File copied successfully to: {$destination}");
				}
			}

			if ($this->create("$className.php", $content, $basePath)) {
				$this->success("✅ Error reporting email file '{$className}' has been successfully created and is ready for use.");
			} else {
				$this->error("❌ Failed to create the file '{$className}.php' at '{$basePath}'.");
			}
		}
	}