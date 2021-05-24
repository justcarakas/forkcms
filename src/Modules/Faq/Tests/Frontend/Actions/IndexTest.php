<?php

namespace ForkCMS\Modules\Faq\Tests\Frontend\Actions;

use ForkCMS\Core\Tests\Frontend\FrontendWebTestCase;
use ForkCMS\Modules\Faq\Tests\DataFixtures\LoadFaqCategories;
use ForkCMS\Modules\Faq\Tests\DataFixtures\LoadFaqQuestions;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexTest extends FrontendWebTestCase
{
    public function testFaqIndexContainsCategories(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadFaqCategories::class,
                LoadFaqQuestions::class,
            ]
        );

        self::assertPageLoadedCorrectly(
            $client,
            '/en/faq',
            [LoadFaqCategories::FAQ_CATEGORY_TITLE]
        );
    }

    public function testFaqIndexContainsQuestions(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadFaqCategories::class,
                LoadFaqQuestions::class,
            ]
        );

        self::assertPageLoadedCorrectly(
            $client,
            '/en/faq',
            [LoadFaqQuestions::FAQ_QUESTION_TITLE]
        );
    }
}
