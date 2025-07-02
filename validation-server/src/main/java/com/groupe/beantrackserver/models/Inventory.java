package com.groupe.beantrackserver.models;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.OneToOne;
import jakarta.persistence.Table;
import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table(name = "Inventory")
public class Inventory {

    @Id
    @Column(name = "id", length = 6)
    private String id;

    // One of these will be null due to CHECK constraint in DB
    @OneToOne
    @JoinColumn(name = "raw_coffee_id")
    private RawCoffee rawCoffee;

    @OneToOne
    @JoinColumn(name = "coffee_product_id")
    private CoffeeProduct coffeeProduct;

    @Column(name = "quantity_in_stock", nullable = false, precision = 10, scale = 2)
    private BigDecimal quantityInStock;

    @ManyToOne
    @JoinColumn(name = "supply_center_id", nullable = false)
    private SupplyCenters supplyCenter;

    @Column(name = "last_updated")
    private LocalDateTime lastUpdated;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    public Inventory() {
    }

    // Getters and Setters
    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public RawCoffee getRawCoffee() {
        return rawCoffee;
    }

    public void setRawCoffee(RawCoffee rawCoffee) {
        this.rawCoffee = rawCoffee;
    }

    public CoffeeProduct getCoffeeProduct() {
        return coffeeProduct;
    }

    public void setCoffeeProduct(CoffeeProduct coffeeProduct) {
        this.coffeeProduct = coffeeProduct;
    }

    public BigDecimal getQuantityInStock() {
        return quantityInStock;
    }

    public void setQuantityInStock(BigDecimal quantityInStock) {
        this.quantityInStock = quantityInStock;
    }

    public SupplyCenters getSupplyCenter() {
        return supplyCenter;
    }

    public void setSupplyCenter(SupplyCenters supplyCenter) {
        this.supplyCenter = supplyCenter;
    }

    public LocalDateTime getLastUpdated() {
        return lastUpdated;
    }

    public void setLastUpdated(LocalDateTime lastUpdated) {
        this.lastUpdated = lastUpdated;
    }

    public LocalDateTime getCreatedAt() {
        return createdAt;
    }

    public void setCreatedAt(LocalDateTime createdAt) {
        this.createdAt = createdAt;
    }

    public LocalDateTime getUpdatedAt() {
        return updatedAt;
    }

    public void setUpdatedAt(LocalDateTime updatedAt) {
        this.updatedAt = updatedAt;
    }
}