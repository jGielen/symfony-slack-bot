<?php
/**
 * This file is part of the WoW-Apps/Symfony-Slack-Bot bundle for Symfony 3
 * https://github.com/wow-apps/symfony-slack-bot
 *
 * (c) 2016 WoW-Apps
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WowApps\SlackBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WowApps\SlackBundle\DTO\SlackMessage;
use WowApps\SlackBundle\Service\SlackBot;
use WowApps\SlackBundle\Traits\SlackMessageTrait;

/**
 * @author Alexey Samara <lion.samara@gmail.com>
 * @package WowApps\SlackBundle
 * @see https://github.com/wow-apps/symfony-slack-bot/wiki/1.-Installation#send-test-message
 */
class SlackbotTestCommand extends ContainerAwareCommand
{
    use SlackMessageTrait;

    /** @var SlackBot */
    private $slackBot;

    /**
     * @param string $name
     * @param SlackBot $slackBot
     */
    public function __construct(string $name, SlackBot $slackBot)
    {
        parent::__construct($name);
        $this->slackBot = $slackBot;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('wowapps:slackbot:test')
            ->setDescription('Test your settings and try to send messages');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->drawHeader($output);

        $symfonyStyle = new SymfonyStyle($input, $output);
        $this->drawConfig($symfonyStyle, $this->slackBot->getConfig());

        $symfonyStyle->section('Sending short message...');

        if (!$this->sendTestMessage()) {
            $symfonyStyle->error('Message not sent');
            return;
        }

        $symfonyStyle->success('Message sent successfully');
    }

    /**
     * @param OutputInterface $output
     */
    private function drawHeader(OutputInterface $output)
    {
        echo PHP_EOL;
        $output->writeln('<bg=blue;options=bold;fg=white>                                               </>');
        $output->writeln('<bg=blue;options=bold;fg=white>           S L A C K B O T   T E S T           </>');
        $output->writeln('<bg=blue;options=bold;fg=white>                                               </>');
        echo PHP_EOL;
    }

    /**
     * @param SymfonyStyle $symfonyStyle
     * @param array $slackBotConfig
     */
    private function drawConfig(SymfonyStyle $symfonyStyle, array $slackBotConfig)
    {
        $symfonyStyle->section('SlackBot general settings');

        $symfonyStyle->table(
            ['api url'],
            [[$slackBotConfig['api_url']]]
        );

        $symfonyStyle->table(
            ['default icon'],
            [[$slackBotConfig['default_icon']]]
        );

        $symfonyStyle->table(
            ['default recipient'],
            [[$slackBotConfig['default_channel']]]
        );

        $symfonyStyle->section('SlackBot quote colors');

        $symfonyStyle->table(
            ['default', 'info', 'warning', 'success', 'danger'],
            [
                [
                    $slackBotConfig['quote_color']['default'],
                    $slackBotConfig['quote_color']['info'],
                    $slackBotConfig['quote_color']['warning'],
                    $slackBotConfig['quote_color']['success'],
                    $slackBotConfig['quote_color']['danger']
                ]
            ]
        );
    }

    /**
     * @return bool
     */
    private function sendTestMessage()
    {
        $slackMessage = new SlackMessage();

        $quoteText = [
            sprintf('This is %s message sent by SlackBot', $this->formatBold('test')),
            $this->formatCode([
                '<?php',
                '$someString = \'Hello world!\';',
                'echo $someString;'
            ])
        ];

        $slackMessage
            ->setIcon('http://cdn.wow-apps.pro/slackbot/slack-bot-icon-48.png')
            ->setText('If you read this - SlackBot is working!')
            ->setRecipient('general')
            ->setSender('WoW-Apps')
            ->setShowQuote(true)
            ->setQuoteType(SlackBot::QUOTE_SUCCESS)
            ->setQuoteText($this->inlineMultilines($quoteText))
            ->setQuoteTitle('SlackBot for Symfony 3')
            ->setQuoteTitleLink('https://github.com/wow-apps/symfony-slack-bot')
        ;

        return $this->slackBot->sendMessage($slackMessage);
    }
}
