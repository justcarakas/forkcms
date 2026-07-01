<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\tests\Backend\Actions;

use ForkCMS\Modules\Backend\DataFixtures\UserFixture;
use ForkCMS\Modules\Backend\DataFixtures\UserGroupFixture;
use ForkCMS\Modules\Backend\tests\BackendWebTestCase;

final class UserEditTest extends BackendWebTestCase
{
    protected const string TEST_URL = '/private/en/backend/user-edit/1';

    public function testPageLoads(): void
    {
        $user = self::loginBackendUser();
        self::assertPageLoadedCorrectly(
            self::TEST_URL,
            $user->displayName . ' | Edit | Users | Settings | Fork CMS | Fork CMS',
            [
                'Display name',
                'E-mail',
                'Super admin (grant access to everything)',
                'Enable CMS access for this account.',
                'Short date format',
                $user->displayName,
                $user->email,
            ]
        );

        self::assertHasLink('Users', '/private/en/backend/user-index');
    }

    public function testEditWithoutChanges(): void
    {
        $user = self::loadPage();
        self::assertEmptyFormSubmission('user', 0, 'Save');
        self::assertCurrentUrlEndsWith('/private/en/backend/user-index');
        self::assertDataGridHasLink($user->email);
        self::assertResponseContains('The settings for "' . $user->displayName . '" were saved.');
    }

    public function testWithInvalidData(): void
    {
        self::loadPage();

        self::submitForm(
            'Save',
            [
                'user[user][tab_Authentication][email]' => 'jelmer.prins',
                'user[user][tab_Authentication][plainTextPassword][first]' => 'I<3ForkCMS',
                'user[user][tab_Authentication][plainTextPassword][second]' => 'I<3ForkCMS',
            ],
            'The password is too short.',
            'Please provide a valid e-mail address.',
        );
    }

    public function testUniqueness(): void
    {
        self::loadPage();

        self::submitForm(
            'Save',
            [
                'user[user][tab_Authentication][displayName]' => 'Super Admin',
                'user[user][tab_Authentication][email]' => UserFixture::SUPER_ADMIN_EMAIL,
            ],
            'This e-mailaddress is in use.',
            'This display name is in use.',
        );
    }

    public function testSubmittedFormRedirectsToIndex(): void
    {
        self::loadPage();

        self::submitForm(
            'Save',
            [
                'user[user][tab_Authentication][displayName]' => 'Jelmer Prins',
                'user[user][tab_Authentication][email]' => 'jelmer.prins@example.com',
                'user[user][tab_Authentication][plainTextPassword][first]' => 'IAbsolutely<3ForkCMS',
                'user[user][tab_Authentication][plainTextPassword][second]' => 'IAbsolutely<3ForkCMS',
            ],
        );
        self::getClient()->followRedirect();
        self::assertCurrentUrlEndsWith('/private/en/backend/user-index');
        self::assertDataGridHasLink('jelmer.prins@example.com');
        self::assertResponseContains('The settings for "Jelmer Prins" were saved.');
    }

    #[\Override]
    protected static function getClassFixtures(): array
    {
        return [
            new UserGroupFixture(),
            new UserFixture(),
        ];
    }
}
