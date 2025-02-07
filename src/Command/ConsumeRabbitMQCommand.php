<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ElasticsearchService;
use App\Service\RabbitMQService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:consume-rabbitmq', description: 'Algo')]
class ConsumeRabbitMQCommand extends Command
{
    protected static $defaultName = 'app:consume-rabbitmq';

    public function __construct(
        private readonly RabbitMQService $rabbitMQService,
        private readonly ElasticsearchService $elasticsearchService,
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
                'message' => 'Test',
                'action' => 'test-user',
                'timestamp' => date('c'),
            ];
            $this->elasticsearchService->index('auditoria-admin', $data);
        });
        dump('Sale');
        return Command::SUCCESS;
    }
}
