# Commission Calculator System

## 📌 What is this?
This project is a **commission calculator system** for financial transactions such as:
- **Cash-in (deposit)**
- **Cash-out (withdrawal)**
- **Loan repayment**

Each transaction has rules for commission fees (percentage + min/max limits). The system:
- Reads transactions from a **CSV file**
- Calculates the correct commission for each transaction
- Allows filtering transactions (by date, user type, operation type)
- Includes unit tests to check rules and parsing logic

The goal is to have a **clean, extensible, testable structure** that can be easily extended with new commission types in the future.

---

## 🛠 Requirements
- PHP 8.1 or newer
- Composer
- (Optional) PHPUnit for running tests

---

## 🚀 How to Run

1. **Install dependencies**
   ```bash
   composer install
   ```
      ```bash
   php index.php
   ```
   
    ```bash
    [2016-01-07] uid:1 private cash_out 100.00 USD  => fee: 0.50 USD
    [2016-01-10] uid:2 business cash_in 1000.00 EUR => fee: 3.00 EUR
    [2016-01-15] uid:3 private loan_repayment 200.00 EUR  => fee: 5.00 EUR
    ```

## 🧪 Run Tests

1. **Tests are written with PHPUnit.**
   ```bash
   composer test
   ```
