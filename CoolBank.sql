-- Create Database
CREATE DATABASE IF NOT EXISTS CoolBank;
USE CoolBank;

-- Customer table
CREATE TABLE Customer (
    customer_id INT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    address VARCHAR(255),
    phone_number VARCHAR(15),
    email VARCHAR(100),
    date_of_birth DATE,
    registration_date DATE
);

-- Branch table
CREATE TABLE Branch (
    branch_id INT PRIMARY KEY,
    branch_name VARCHAR(100),
    location VARCHAR(255),
    phone_number VARCHAR(15)
);

-- Account Superclass table
CREATE TABLE Account (
    account_number INT PRIMARY KEY,
    customer_id INT,
    branch_id INT,
    balance DECIMAL(15, 2),
    creation_date DATE,
    status VARCHAR(20),
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE CASCADE ON UPDATE CASCADE, 
    FOREIGN KEY (branch_id) REFERENCES Branch(branch_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Savings Account Subclass (IS A Account)
CREATE TABLE SavingsAccount (
    account_number INT PRIMARY KEY,
    interest_rate DECIMAL(5, 2),
    FOREIGN KEY (account_number) REFERENCES Account(account_number) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Checking Account Subclass (IS A Account)
CREATE TABLE CheckingAccount (
    account_number INT PRIMARY KEY,
    overdraft_limit DECIMAL(15, 2),
    FOREIGN KEY (account_number) REFERENCES Account(account_number) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Loan Account Subclass (IS A Account)
CREATE TABLE LoanAccount (
    account_number INT PRIMARY KEY,
    loan_term INT,
    loan_amount DECIMAL(15, 2),
    interest_rate DECIMAL(5, 2),
    FOREIGN KEY (account_number) REFERENCES Account(account_number) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Transaction table
CREATE TABLE Transaction (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    account_number INT,
    transaction_type VARCHAR(20),
    amount DECIMAL(15, 2),
    transaction_date DATE,
    description VARCHAR(255),
    FOREIGN KEY (account_number) REFERENCES Account(account_number) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Loan(for non-account-related loans) table
CREATE TABLE Loan (
    loan_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    loan_amount DECIMAL(15, 2),
    interest_rate DECIMAL(5, 2),
    loan_term INT,
    start_date DATE,
    end_date DATE,
    branch_id INT,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES Branch(branch_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Employee table
CREATE TABLE Employee (
    employee_id INT PRIMARY KEY,
    branch_id INT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    position VARCHAR(50),
    hire_date DATE,
    salary DECIMAL(15, 2),
    FOREIGN KEY (branch_id) REFERENCES Branch(branch_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Manages(relationship between Employee and Loan) table
CREATE TABLE Manages (
    employee_id INT,
    loan_id INT,
    PRIMARY KEY (employee_id, loan_id),
    FOREIGN KEY (employee_id) REFERENCES Employee(employee_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (loan_id) REFERENCES Loan(loan_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- trigger1 check for the balance 
DELIMITER //
CREATE TRIGGER log_balance_update
AFTER UPDATE ON Account
FOR EACH ROW
BEGIN
    -- Log only when the balance is updated
    IF NEW.balance <> OLD.balance THEN
        INSERT INTO Transaction (account_number, transaction_type, amount, transaction_date, description)
        VALUES (NEW.account_number, 'Balance Update', NEW.balance, CURDATE(), 'Balance updated automatically');
    END IF;
END;
//
DELIMITER ;

-- trigger2 to check for the statues - active/inactive 
DELIMITER //
CREATE TRIGGER account_status_update
AFTER UPDATE ON Account
FOR EACH ROW
BEGIN
    -- Log the status update (only if the status has changed)
    IF NEW.status <> OLD.status THEN
        INSERT INTO Transaction (account_number, transaction_type, amount, transaction_date, description)
        VALUES (NEW.account_number, 'Status Update', 0.00, CURDATE(), CONCAT('Account status changed from ', OLD.status, ' to ', NEW.status));
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE ReserveLoan (
    IN in_customer_id INT,
    IN in_loan_amount DECIMAL(15,2),
    IN in_interest_rate DECIMAL(5,2),
    IN in_loan_term INT,
    IN in_branch_id INT
)
BEGIN
    -- Ensure the customer exists
    IF NOT EXISTS (SELECT 1 FROM Customer WHERE customer_id = in_customer_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Customer does not exist';
    END IF;

    -- Insert loan record
    INSERT INTO Loan (customer_id, loan_amount, interest_rate, loan_term, start_date, end_date, branch_id)
    VALUES (in_customer_id, in_loan_amount, in_interest_rate, in_loan_term, CURDATE(), DATE_ADD(CURDATE(), INTERVAL in_loan_term YEAR), in_branch_id);
END;
//
DELIMITER ;

-- MİSSİNG İNSERT TABLE STATEMENTS
INSERT INTO Customer (customer_id, first_name, last_name, address, phone_number, email, date_of_birth, registration_date)
VALUES 
(1, 'Alice', 'Smith', '123 Elm St', '5551234567', 'alice.smith@example.com', '1985-06-15', CURDATE()),
(2, 'Bob', 'Jones', '456 Oak St', '5557654321', 'bob.jones@example.com', '1990-08-20', CURDATE()),
(3, 'Charlie', 'Brown', '789 Pine St', '5556781234', 'charlie.brown@example.com', '1982-11-05', CURDATE()),
(4, 'Daisy', 'White', '321 Maple St', '5554327890', 'daisy.white@example.com', '1995-04-12', CURDATE()),
(5, 'Edward', 'Green', '654 Cedar St', '5559876543', 'edward.green@example.com', '1978-03-27', CURDATE());


INSERT INTO Branch (branch_id, branch_name, location, phone_number)
VALUES 
(1, 'Central Branch', 'Downtown', '5559876543'),
(2, 'East Branch', 'Uptown', '5558765432'),
(3, 'West Branch', 'Suburb', '5556543210'),
(4, 'North Branch', 'Northside', '5551237890'),
(5, 'South Branch', 'Southside', '5557890123');


INSERT INTO Account (account_number, customer_id, branch_id, balance, creation_date, status)
VALUES 
(1, 1, 1, 5000.00, CURDATE(), 'Active'),
(2, 2, 2, 3000.00, CURDATE(), 'Active'),
(3, 3, 3, 10000.00, CURDATE(), 'Active'),
(4, 4, 4, 7500.00, CURDATE(), 'Inactive'),
(5, 5, 5, 1500.00, CURDATE(), 'Active');


INSERT INTO SavingsAccount (account_number, interest_rate)
VALUES 
(1, 2.5),
(2, 3.0),
(3, 1.8),
(4, 2.0),
(5, 2.7);

INSERT INTO CheckingAccount (account_number, overdraft_limit)
VALUES 
(1, 1000.00),
(2, 1500.00),
(3, 500.00),
(4, 2000.00),
(5, 750.00);

INSERT INTO Employee (employee_id, branch_id, first_name, last_name, position, hire_date, salary)
VALUES 
(1, 1, 'Jane', 'Doe', 'Manager', '2015-01-01', 75000.00),
(2, 2, 'John', 'Smith', 'Clerk', '2020-06-01', 45000.00),
(3, 3, 'Emily', 'Johnson', 'Teller', '2018-09-15', 40000.00),
(4, 4, 'Michael', 'Brown', 'Supervisor', '2016-05-20', 60000.00),
(5, 5, 'Olivia', 'Davis', 'Assistant Manager', '2017-12-10', 55000.00);

INSERT INTO Loan (customer_id, loan_amount, interest_rate, loan_term, start_date, end_date, branch_id)
VALUES 
(1, 10000.00, 5.5, 10, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 10 YEAR), 1),
(2, 5000.00, 4.5, 5, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 YEAR), 2),
(3, 15000.00, 6.0, 15, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 YEAR), 3),
(4, 7000.00, 5.0, 7, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 YEAR), 4),
(5, 8000.00, 4.8, 8, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 8 YEAR), 5);

INSERT INTO Manages (employee_id, loan_id)
VALUES 
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

INSERT INTO Transaction (account_number, transaction_type, amount, transaction_date, description)
VALUES 
(1, 'Deposit', 2000.00, CURDATE(), 'Initial deposit'),
(2, 'Withdrawal', 500.00, CURDATE(), 'ATM withdrawal'),
(3, 'Deposit', 3000.00, CURDATE(), 'Salary deposit'),
(4, 'Transfer', 1000.00, CURDATE(), 'Transfer to another account'),
(5, 'Fee', 50.00, CURDATE(), 'Account maintenance fee');

-- CHECKNG FOR THE SUCCESS OF TABLES CREATİON ONLY
-- SELECT * FROM Customer;
-- SELECT * FROM Branch;
-- SELECT * FROM Account;
-- SELECT * FROM SavingsAccount;
-- SELECT * FROM CheckingAccount;
-- SELECT * FROM Loan;
-- SELECT * FROM Employee;
-- SELECT * FROM Manages;
-- SELECT * FROM Transaction;

-- Test balance update (triggers log_balance_update)
UPDATE Account SET balance = 4500.00 WHERE account_number = 1;

-- test for trigger2 
UPDATE Account
SET status = 'Closed'
WHERE account_number = 1;

-- CALL ReserveLoan(999, 3000.00, 5.00, 5, 1);  -- Non-existing customer (customer_id = 999) ERROR! 
-- CALL ReserveLoan(1, 2000.00, 5.00, 5, 999);  -- Non-existing brach-id (branch_id = 999) ERROR!

-- Test loan reservation procedure 
-- CALL ReserveLoan(1, 10000.00, 5.5, 10, 1);

-- View the Transaction table to verify the results
SELECT * FROM Account; -- observing for the first value (5000) and after the update part, see the transaction part. 
-- SELECT * FROM Transaction; -- additional line in the transaction should be observed (automatically as a balanced update)create 
