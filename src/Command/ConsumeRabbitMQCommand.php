<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\RabbitMQService;
use App\Service\ElasticsearchService;

#[AsCommand(name: 'app:consume-rabbitmq', description: 'Algo')]
class ConsumeRabbitMQCommand extends Command
{
    protected static $defaultName = 'app:consume-rabbitmq';

    public function __construct(
        private RabbitMQService $rabbitMQService,
        private ElasticsearchService $elasticsearchService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Consume mensajes de RabbitMQ y los indexa en Elasticsearch.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        dump('Entra');

        $this->rabbitMQService->consumeSingleMessage('user-notifications', function ($message) {
            $data = [
                'messasge' => 'Test',
                'action' => 'test-user',
                'timestamp' => date('c'),
            ];
            $this->elasticsearchService->index('auditoria-admin', $data);
        });
        dump('Sale');
        return Command::SUCCESS;
    }
}
