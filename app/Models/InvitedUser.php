<?php

/*
 * InvitedUser.php
 * Copyright (c) 2022 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace FireflyIII\Models;

use FireflyIII\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InvitedUser
 *
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|InvitedUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvitedUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvitedUser query()
 * @mixin \Eloquent
 */
class InvitedUser extends Model
{
    protected $fillable = ['user_id', 'email', 'invite_code', 'expires', 'redeemed'];

    protected $casts
        = [
            'expires'  => 'datetime',
            'redeemed' => 'boolean',
        ];

    /**
     * @codeCoverageIgnore
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
