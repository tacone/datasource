<?php

namespace Tacone\DataSource\Test;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
}
