<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test email functionality with debug information',
)]
class TestEmailCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $email = (new Email())
                ->from('mythos.official540@gmail.com')
                ->to('mythos.official540@gmail.com')
                ->subject('Test Email from Mythos Planner - ' . date('Y-m-d H:i:s'))
                ->html('<p>This is a test email sent at ' . date('Y-m-d H:i:s') . '</p>');

            $io->note('Attempting to send email...');
            $this->mailer->send($email);
            $io->success('Test email sent successfully!');
            
            return Command::SUCCESS;
        } catch (TransportExceptionInterface $e) {
            $io->error([
                'Failed to send email',
                'Error: ' . $e->getMessage(),
                'Debug info: ' . get_class($e)
            ]);
            return Command::FAILURE;
        } catch (\Exception $e) {
            $io->error([
                'Unexpected error while sending email',
                'Error: ' . $e->getMessage(),
                'Type: ' . get_class($e)
            ]);
            return Command::FAILURE;
        }
    }
}
