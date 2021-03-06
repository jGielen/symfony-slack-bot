<?php
/**
 * This file is part of the WoW-Apps/Symfony-Slack-Bot bundle for Symfony 3
 * https://github.com/wow-apps/symfony-slack-bot
 *
 * (c) 2018 WoW-Apps
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WowApps\SlackBundle\Tests\Service;

use WowApps\SlackBundle\Service\SlackBot;
use WowApps\SlackBundle\Service\SlackMessageValidator;
use WowApps\SlackBundle\Tests\TestCase;

/**
 * Class SlackBotTest
 * @author Alexey Samara <lion.samara@gmail.com>
 * @package WowApps\SlackBundle
 */
class SlackBotTest extends TestCase
{
    /** @var SlackBot */
    private $slackBotService;

    /** @var array */
    private $options = [];

    public function setUp()
    {
        parent::setUp();
        $this->options = [
            "api_url"         => $this->randomString(),
            "default_icon"    => $this->randomString(),
            "default_channel" => $this->randomString(),
            "quote_color"     => [
                "default" => $this->randomString(),
                "info"    => $this->randomString(),
                "warning" => $this->randomString(),
                "success" => $this->randomString(),
                "danger"  => $this->randomString(),
            ],
        ];

        $this->slackBotService = new SlackBot($this->options, new SlackMessageValidator());
    }

    public function testGetConfig()
    {
        $this->assertInternalType('array', $this->slackBotService->getConfig());
        $this->assertSame($this->options, $this->slackBotService->getConfig());
    }

    public function testSetConfig()
    {
        $tempConfig = [$this->randomString()];
        $this->slackBotService->setConfig($tempConfig);
        $this->assertSame($tempConfig, $this->slackBotService->getConfig());

        $this->slackBotService->setConfig($this->options); // rollback config
    }

    public function testQuoteTypeColor()
    {
        $randomType = array_rand(SlackBot::QUOTE_MAP, 1);
        $this->assertInternalType('string', $this->slackBotService->quoteTypeColor($randomType));

        $this->assertSame(
            $this->options['quote_color'][SlackBot::QUOTE_MAP[$randomType]],
            $this->slackBotService->quoteTypeColor($randomType)
        );

        $unExistType = 0;

        foreach (range(0, 999) as $number) {
            if (!array_key_exists($number, SlackBot::QUOTE_MAP)) {
                $unExistType = $number;
                break;
            }

            continue;
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->slackBotService->quoteTypeColor($unExistType);
    }
}
