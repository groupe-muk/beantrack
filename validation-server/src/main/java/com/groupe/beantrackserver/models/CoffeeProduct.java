package com.groupe.beantrackserver.models;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.OneToOne; // Assuming one RawCoffee can produce one CoffeeProduct for simplicity, adjust if needed
import jakarta.persistence.Table;
import java.time.LocalDate;
import java.time.LocalDateTime;

@Entity
@Table(name = "CoffeeProduct")
public class CoffeeProduct {

    @Id
    @Column(name = "id", length = 6)
    private String id;

    @OneToOne // A CoffeeProduct is derived from one RawCoffee
    @JoinColumn(name = "raw_coffee_id", nullable = false, unique = true) // raw_coffee_id is FK and unique
    private RawCoffee rawCoffee;

    @Column(name = "category", nullable = false, length = 50)
    private String category;

    @Column(name = "name", nullable = false, length = 100)
    private String name;

    @Column(name = "product_form", nullable = false, length = 50)
    private String productForm;

    @Column(name = "roast_level", length = 20)
    private String roastLevel;

    @Column(name = "production_date")
    private LocalDate productionDate;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    public CoffeeProduct() {
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

    public String getCategory() {
        return category;
    }

    public void setCategory(String category) {
        this.category = category;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getProductForm() {
        return productForm;
    }

    public void setProductForm(String productForm) {
        this.productForm = productForm;
    }

    public String getRoastLevel() {
        return roastLevel;
    }

    public void setRoastLevel(String roastLevel) {
        this.roastLevel = roastLevel;
    }

    public LocalDate getProductionDate() {
        return productionDate;
    }

    public void setProductionDate(LocalDate productionDate) {
        this.productionDate = productionDate;
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