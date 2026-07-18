<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use App\Traits\QueryScopesTrait;

/**
 * App UserModel — extends Shield's UserModel.
 *
 * Keep this class thin. All auth-related persistence logic
 * is handled by ShieldUserModel. Add app-specific scopes,
 * searchable fields, or helper methods here only when needed.
 */
class UserModel extends ShieldUserModel
{
    use QueryScopesTrait;

    /**
     * Fields used by QueryScopesTrait::search() for LIKE queries.
     * Inherited classes may override this when using BaseService::findAll()
     * with a search filter.
     */
    protected array $searchableFields = ['username', 'email'];
}
