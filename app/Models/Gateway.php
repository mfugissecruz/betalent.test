<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property boolean $is_active
 * @property int $priority
 */
class Gateway extends Model
{
    /** @use HasFactory<\Database\Factories\GatewayFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'bollean',
            'priority'  => 'integer',
        ];
    }
}
