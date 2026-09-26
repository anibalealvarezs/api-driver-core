<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Interfaces;

/**
 * Interface MultiAccountAuthProviderInterface
 *
 * Defines the contract for an Authentication Provider that manages multiple
 * distinct account connections under a single project (e.g., Mailchimp, Klaviyo,
 * Shopify multi-store, NetSuite, ShipStation).
 */
interface MultiAccountAuthProviderInterface extends AuthProviderInterface
{
    /**
     * Get all configured accounts and their credential descriptors.
     *
     * @return array<string, array{account_id: string, name: ?string, is_valid: bool}>
     */
    public function getAccounts(): array;

    /**
     * Retrieve credentials for a specific account identifier.
     *
     * @param string $accountId
     * @return array<string, mixed>|null
     */
    public function getCredentialsForAccount(string $accountId): ?array;

    /**
     * Store or update credentials for a specific account.
     *
     * @param string $accountId
     * @param array<string, mixed> $credentials
     * @return void
     */
    public function storeAccountCredentials(string $accountId, array $credentials): void;

    /**
     * Remove credentials for an account.
     *
     * @param string $accountId
     * @return void
     */
    public function removeAccountCredentials(string $accountId): void;
}
