<?php
namespace Makasim\Yadm\Bundle\Command;

use Makasim\Yadm\Bundle\Snapshotter;
use Makasim\Yadm\Registry;
use Makasim\Yadm\Storage;
use MongoDB\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCollectionsSnapshotsCommand extends Command
{
    public static $defaultName = 'yadm:make-collections-snapshots';

    /**
     * @var Registry
     */
    private $yadm;

    /**
     * @var Client
     */
    private $client;

    public function __construct(?string $name = null, Registry $yadm, Client $client)
    {
        parent::__construct($name);
        $this->yadm = $yadm;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('yadm:make-collections-snapshots')
            ->setDescription('Makes snapshots of mongodb collections')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);

        $logger->debug('Make snapshots of mongodb collections');

        $snapshotter = new Snapshotter($this->client);
        $processedCollections = [];
        foreach ($this->yadm->getStorages() as $name => $storage) {
            /** @var Storage $storage */

            $collection = $storage->getCollection();

            $collection->getCollectionName();

            if (isset($processedCollections[$collection->getCollectionName()])) {
                continue;
            }

            $snapshotter->make($collection, $logger);

            $processedCollections[$collection->getCollectionName()] = true;
        }

        $logger->debug('Done');
    }
}
