<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoffeeProduct extends Model
{
    use HasFactory;

    protected $table = 'coffee_product';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'raw_coffee_id', 'category', 'name', 'product_form', 'roast_level', 'production_date'
    ];

    public function rawCoffee()
    {
        return $this->belongsTo(RawCoffee::class, 'raw_coffee_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'coffee_product_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'coffee_product_id');
    }

    /**
     * Calculate price for a given quantity
     */
    public function calculatePrice($quantity)
    {
        // Base price calculation logic based on product category and roast level
        $basePrice = 1000; // Base price per kg
        
        // Adjust price based on category
        switch(strtolower($this->category ?? '')) {
            case 'premium':
                $basePrice = 5.04;
                break;
            case 'specialty':
                $basePrice = 4.20;
                break;
            case 'standard':
            default:
                $basePrice = 3.04;
                break;
        }
        
        // Adjust price based on roast level
        switch(strtolower($this->roast_level ?? '')) {
            case 'dark':
                $basePrice *= 1.1; // 10% increase for dark roast
                break;
            case 'medium':
                $basePrice *= 1.05; // 5% increase for medium roast
                break;
            case 'light':
            default:
                // No adjustment for light roast
                break;
        }
        
        $markup = 0.20; // 20% markup
        $pricePerKg = $basePrice + ($basePrice * $markup);
        
        // Round to avoid decimal issues and ensure whole numbers
        return round($pricePerKg * $quantity);
    }
    
    /**
     * Get base price per kg for this product
     */
    public function getBasePricePerKg()
    {
        return $this->calculatePrice(1);
    }

    /**
     * Get the markup percentage
     */
    public function getMarkupPercentage()
    {
        return 0.20; // 20% markup
    }

    /**
     * Get the final price per kg including markup
     */
    public function getPricePerKg()
    {
        $basePrice = $this->getBasePricePerKg();
        $markup = $this->getMarkupPercentage();
        
        return $basePrice + ($basePrice * $markup);
    }
}
