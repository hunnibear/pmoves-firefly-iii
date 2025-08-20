#!/usr/bin/env python3
"""
Comprehensive Sample Data Creator for Couples Dashboard & Agentic Testing
Creates realistic financial data for testing watch folders and agent features
"""

import json
import csv
import random
from datetime import datetime, timedelta
from pathlib import Path

class SampleDataCreator:
    def __init__(self, base_path="watch-folders"):
        self.base_path = Path(base_path)
        self.partners = ["Alex", "Sam", "Joint"]
        self.categories = {
            "Food & Dining": ["Restaurant", "Coffee Shop", "Grocery Store", "Fast Food", "Delivery"],
            "Groceries": ["Supermarket", "Farmers Market", "Bulk Store", "Organic Market"],
            "Transportation": ["Gas Station", "Uber", "Taxi", "Parking", "Car Service"],
            "Shopping": ["Amazon", "Department Store", "Online Purchase", "Boutique"],
            "Bills & Utilities": ["Electric", "Gas", "Water", "Internet", "Phone"],
            "Entertainment": ["Movies", "Concert", "Streaming", "Games", "Books"],
            "Health & Fitness": ["Gym", "Doctor", "Pharmacy", "Dentist", "Therapy"],
            "Home Improvement": ["Hardware Store", "Furniture", "Appliances", "Repairs"],
            "Pet Care": ["Vet", "Pet Store", "Grooming", "Pet Food"],
            "Travel": ["Hotel", "Flight", "Car Rental", "Travel Insurance"]
        }
        
    def generate_transaction_amount(self, category):
        """Generate realistic amounts based on category"""
        ranges = {
            "Food & Dining": (8, 120),
            "Groceries": (25, 250),
            "Transportation": (3, 80),
            "Shopping": (15, 300),
            "Bills & Utilities": (50, 400),
            "Entertainment": (10, 150),
            "Health & Fitness": (20, 300),
            "Home Improvement": (30, 500),
            "Pet Care": (25, 200),
            "Travel": (100, 2000)
        }
        min_amt, max_amt = ranges.get(category, (10, 100))
        return round(random.uniform(min_amt, max_amt), 2)
    
    def create_bank_statement_csv(self, filename, days=30):
        """Create a realistic bank statement CSV"""
        filepath = self.base_path / "bank-statements" / filename
        filepath.parent.mkdir(parents=True, exist_ok=True)
        
        transactions = []
        current_date = datetime.now() - timedelta(days=days)
        
        for day in range(days):
            date = current_date + timedelta(days=day)
            
            # Generate 1-5 transactions per day
            num_transactions = random.randint(1, 5)
            
            for _ in range(num_transactions):
                category = random.choice(list(self.categories.keys()))
                vendor_type = random.choice(self.categories[category])
                amount = self.generate_transaction_amount(category)
                partner = random.choice(self.partners)
                
                # Create realistic description
                if category == "Food & Dining":
                    descriptions = [
                        f"{vendor_type} - Downtown",
                        f"{vendor_type} #1234",
                        f"{vendor_type} - Lunch",
                        f"{vendor_type} - Date Night"
                    ]
                elif category == "Groceries":
                    descriptions = [
                        f"{vendor_type} - Weekly Shop",
                        f"{vendor_type} Store",
                        f"Organic {vendor_type}",
                        f"{vendor_type} - Bulk Buy"
                    ]
                else:
                    descriptions = [
                        f"{vendor_type} Service",
                        f"{vendor_type} - Monthly",
                        f"{vendor_type} Purchase",
                        f"{vendor_type} - Emergency"
                    ]
                
                description = random.choice(descriptions)
                
                transaction = {
                    "Date": date.strftime("%Y-%m-%d"),
                    "Description": description,
                    "Amount": f"-{amount}",
                    "Category": category,
                    "Partner": partner
                }
                transactions.append(transaction)
        
        # Sort by date
        transactions.sort(key=lambda x: x["Date"])
        
        # Write CSV
        with open(filepath, 'w', newline='') as csvfile:
            fieldnames = ["Date", "Description", "Amount", "Category", "Partner"]
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            writer.writeheader()
            writer.writerows(transactions)
        
        print(f"✅ Created bank statement: {filepath} ({len(transactions)} transactions)")
        return filepath
    
    def create_receipt_text(self, filename, receipt_type="grocery"):
        """Create a realistic receipt text file"""
        filepath = self.base_path / "receipts" / filename
        filepath.parent.mkdir(parents=True, exist_ok=True)
        
        if receipt_type == "grocery":
            content = self._create_grocery_receipt()
        elif receipt_type == "restaurant":
            content = self._create_restaurant_receipt()
        elif receipt_type == "gas":
            content = self._create_gas_receipt()
        else:
            content = self._create_retail_receipt()
        
        with open(filepath, 'w') as f:
            f.write(content)
        
        print(f"✅ Created receipt: {filepath}")
        return filepath
    
    def _create_grocery_receipt(self):
        stores = ["Fresh Market", "Organic Foods", "Super Saver", "Green Grocer"]
        store = random.choice(stores)
        
        items = [
            ("Bananas (organic)", random.uniform(2, 6)),
            ("Bread - Whole Grain", random.uniform(3, 7)),
            ("Milk - 2% Gallon", random.uniform(3, 6)),
            ("Chicken Breast", random.uniform(8, 15)),
            ("Broccoli", random.uniform(2, 5)),
            ("Cheese - Swiss", random.uniform(4, 9)),
            ("Yogurt - Greek", random.uniform(4, 8)),
            ("Apples - Honeycrisp", random.uniform(3, 8)),
            ("Rice - Brown", random.uniform(5, 10)),
            ("Olive Oil - Extra Virgin", random.uniform(6, 12))
        ]
        
        selected_items = random.sample(items, random.randint(5, 8))
        
        subtotal = sum(price for _, price in selected_items)
        tax = subtotal * 0.06
        total = subtotal + tax
        
        receipt = f"""========================
      GROCERY RECEIPT
========================
Store: {store}
Address: {random.randint(100, 999)} Main St
Date: {datetime.now().strftime('%Y-%m-%d')}
Time: {datetime.now().strftime('%H:%M:%S')}
========================

"""
        
        for item, price in selected_items:
            receipt += f"{item:<25} ${price:.2f}\n"
        
        receipt += f"""
========================
Subtotal:              ${subtotal:.2f}
Tax:                   ${tax:.2f}
========================
TOTAL:                 ${total:.2f}
========================

Payment: Credit Card
Card: ****{random.randint(1000, 9999)}
Auth: {random.randint(100000, 999999)}

Thank you for shopping!
{store}
Visit us again soon!

Receipt #: {store[:2].upper()}-{datetime.now().strftime('%y%m%d-%H%M')}"""
        
        return receipt
    
    def _create_restaurant_receipt(self):
        restaurants = [
            "Tony's Italian Bistro", "The Garden Cafe", "Steakhouse Prime", 
            "Sushi Zen", "Mumbai Spice", "French Quarter"
        ]
        restaurant = random.choice(restaurants)
        
        appetizers = [("Caesar Salad", 8.95), ("Bruschetta", 7.50), ("Wings", 11.95)]
        entrees = [("Grilled Salmon", 24.95), ("Pasta Primavera", 18.95), ("Ribeye Steak", 32.95)]
        beverages = [("House Wine", 8.00), ("Craft Beer", 6.50), ("Soda", 3.50)]
        
        selected_items = []
        selected_items.append(random.choice(appetizers))
        selected_items.extend(random.sample(entrees, 2))
        selected_items.extend(random.sample(beverages, 2))
        
        subtotal = sum(price for _, price in selected_items)
        tax = subtotal * 0.085
        tip = subtotal * 0.18
        total = subtotal + tax + tip
        
        receipt = f"""========================
      RESTAURANT RECEIPT
========================
{restaurant}
{random.randint(100, 999)} {random.choice(['Oak', 'Pine', 'Main', 'First'])} Avenue
Date: {datetime.now().strftime('%Y-%m-%d')}
Time: {datetime.now().strftime('%H:%M:%S')}
Server: {random.choice(['Maria', 'John', 'Sarah', 'Mike'])}
========================

Table {random.randint(10, 25)} - Party of 2

"""
        
        for item, price in selected_items:
            receipt += f"{item:<25} ${price:.2f}\n"
        
        receipt += f"""
========================
Subtotal:              ${subtotal:.2f}
Tax (8.5%):            ${tax:.2f}
Tip (18%):             ${tip:.2f}
========================
TOTAL:                 ${total:.2f}
========================

Payment: Credit Card
Card: ****{random.randint(1000, 9999)}
Auth: {random.randint(100000, 999999)}

Thank you! Come back soon!
{restaurant}

Receipt #: {restaurant[:3].upper()}-{datetime.now().strftime('%y%m%d-%H%M')}"""
        
        return receipt
    
    def _create_gas_receipt(self):
        stations = ["Shell", "Exxon", "BP", "Chevron", "Mobil"]
        station = random.choice(stations)
        
        gallons = random.uniform(8, 18)
        price_per_gallon = random.uniform(3.20, 4.10)
        total = gallons * price_per_gallon
        
        receipt = f"""========================
      GAS STATION RECEIPT
========================
{station} Station #1247
{random.randint(100, 999)} Highway Blvd
Date: {datetime.now().strftime('%Y-%m-%d')}
Time: {datetime.now().strftime('%H:%M:%S')}
========================

Pump: {random.randint(1, 8)}
Product: Regular Unleaded

Gallons:               {gallons:.3f}
Price/Gallon:          ${price_per_gallon:.3f}

========================
TOTAL:                 ${total:.2f}
========================

Payment: Credit Card
Card: ****{random.randint(1000, 9999)}
Auth: {random.randint(100000, 999999)}

Thank you!
Drive safely!

Receipt #: {station[:2].upper()}-{datetime.now().strftime('%y%m%d-%H%M')}"""
        
        return receipt
    
    def create_utility_bill(self, filename, bill_type="electric"):
        """Create a realistic utility bill"""
        filepath = self.base_path / "documents" / filename
        filepath.parent.mkdir(parents=True, exist_ok=True)
        
        if bill_type == "electric":
            content = self._create_electric_bill()
        elif bill_type == "gas":
            content = self._create_gas_bill()
        elif bill_type == "water":
            content = self._create_water_bill()
        else:
            content = self._create_internet_bill()
        
        with open(filepath, 'w') as f:
            f.write(content)
        
        print(f"✅ Created utility bill: {filepath}")
        return filepath
    
    def _create_electric_bill(self):
        usage = random.randint(600, 1200)
        rate = random.uniform(0.10, 0.15)
        service_charge = 25.00
        delivery = random.uniform(15, 25)
        taxes = random.uniform(8, 15)
        
        energy_cost = usage * rate
        total = service_charge + energy_cost + delivery + taxes
        
        bill = f"""========================
     ELECTRIC BILL
========================
Metro Power Company
{random.randint(100, 999)} Industrial Blvd
Customer Service: 1-800-POWER-1
========================

BILL SUMMARY
Account: {random.randint(100000000, 999999999)}
Service Address: {random.randint(100, 999)} {random.choice(['Maple', 'Oak', 'Pine'])} Street
Billing Period: {(datetime.now() - timedelta(days=30)).strftime('%b %d')} - {datetime.now().strftime('%b %d, %Y')}

Previous Balance:       ${random.uniform(100, 200):.2f}
Payment Received:      -${random.uniform(100, 200):.2f}
Balance Forward:        $0.00

CURRENT CHARGES
Electric Service Charge: ${service_charge:.2f}
Energy Usage ({usage} kWh): ${energy_cost:.2f}
  Rate: ${rate:.3f} per kWh
Delivery Charges:       ${delivery:.2f}
Taxes & Fees:          ${taxes:.2f}

========================
TOTAL AMOUNT DUE:      ${total:.2f}
========================

DUE DATE: {(datetime.now() + timedelta(days=25)).strftime('%B %d, %Y')}

Payment Options:
- Online: www.metropower.com
- Phone: 1-800-PAY-BILL
- Mail: P.O. Box 12345

Late Payment Fee: $25.00
Disconnect Notice: 10 days

Thank you for your business!

Account #: {random.randint(100000000, 999999999)}
Bill Date: {datetime.now().strftime('%B %d, %Y')}"""
        
        return bill
    
    def _create_gas_bill(self):
        usage = random.randint(50, 150)  # therms
        rate = random.uniform(0.80, 1.20)
        service_charge = 15.00
        delivery = random.uniform(10, 20)
        taxes = random.uniform(5, 12)
        
        gas_cost = usage * rate
        total = service_charge + gas_cost + delivery + taxes
        
        bill = f"""========================
        GAS BILL
========================
City Gas & Electric
{random.randint(100, 999)} Utility Way
Customer Service: 1-800-GAS-HEAT
========================

BILL SUMMARY
Account: {random.randint(100000000, 999999999)}
Service Address: {random.randint(100, 999)} {random.choice(['Maple', 'Oak', 'Pine'])} Street
Billing Period: {(datetime.now() - timedelta(days=30)).strftime('%b %d')} - {datetime.now().strftime('%b %d, %Y')}

Previous Balance:       ${random.uniform(50, 150):.2f}
Payment Received:      -${random.uniform(50, 150):.2f}
Balance Forward:        $0.00

CURRENT CHARGES
Gas Service Charge:     ${service_charge:.2f}
Gas Usage ({usage} therms): ${gas_cost:.2f}
  Rate: ${rate:.3f} per therm
Delivery Charges:       ${delivery:.2f}
Taxes & Fees:          ${taxes:.2f}

========================
TOTAL AMOUNT DUE:      ${total:.2f}
========================

DUE DATE: {(datetime.now() + timedelta(days=25)).strftime('%B %d, %Y')}

Payment Options:
- Online: www.citygas.com
- Phone: 1-800-PAY-GAS
- Auto Pay Available

Late Payment Fee: $15.00

Account #: {random.randint(100000000, 999999999)}
Bill Date: {datetime.now().strftime('%B %d, %Y')}"""
        
        return bill
    
    def _create_water_bill(self):
        usage = random.randint(3000, 8000)  # gallons
        rate = random.uniform(0.004, 0.008)
        service_charge = 12.00
        sewer = random.uniform(15, 30)
        taxes = random.uniform(3, 8)
        
        water_cost = usage * rate
        total = service_charge + water_cost + sewer + taxes
        
        bill = f"""========================
       WATER BILL
========================
Municipal Water Authority
{random.randint(100, 999)} Water St
Customer Service: 1-800-H2O-BILL
========================

BILL SUMMARY
Account: {random.randint(100000000, 999999999)}
Service Address: {random.randint(100, 999)} {random.choice(['Maple', 'Oak', 'Pine'])} Street
Billing Period: {(datetime.now() - timedelta(days=30)).strftime('%b %d')} - {datetime.now().strftime('%b %d, %Y')}

Previous Balance:       ${random.uniform(30, 80):.2f}
Payment Received:      -${random.uniform(30, 80):.2f}
Balance Forward:        $0.00

CURRENT CHARGES
Water Service Charge:   ${service_charge:.2f}
Water Usage ({usage:,} gallons): ${water_cost:.2f}
  Rate: ${rate:.4f} per gallon
Sewer Service:          ${sewer:.2f}
Taxes & Fees:          ${taxes:.2f}

========================
TOTAL AMOUNT DUE:      ${total:.2f}
========================

DUE DATE: {(datetime.now() + timedelta(days=30)).strftime('%B %d, %Y')}

Payment Options:
- Online: www.municipalwater.gov
- Phone: 1-800-H2O-BILL
- Drop Box: City Hall

Account #: {random.randint(100000000, 999999999)}
Bill Date: {datetime.now().strftime('%B %d, %Y')}"""
        
        return bill
    
    def _create_internet_bill(self):
        plans = [
            ("Basic Internet 100 Mbps", 49.99),
            ("High Speed 300 Mbps", 79.99),
            ("Premium Fiber 1 Gig", 99.99),
            ("Ultra Fiber 2 Gig", 129.99)
        ]
        
        plan_name, plan_cost = random.choice(plans)
        equipment_fee = 10.00
        taxes = plan_cost * 0.08
        total = plan_cost + equipment_fee + taxes
        
        bill = f"""========================
     INTERNET BILL
========================
FastNet Communications
{random.randint(100, 999)} Tech Blvd
Customer Service: 1-800-FASTNET
========================

BILL SUMMARY
Account: {random.randint(100000000, 999999999)}
Service Address: {random.randint(100, 999)} {random.choice(['Maple', 'Oak', 'Pine'])} Street
Billing Period: {(datetime.now() - timedelta(days=30)).strftime('%b %d')} - {datetime.now().strftime('%b %d, %Y')}

Previous Balance:       $0.00
Payments & Credits:     $0.00
Balance Forward:        $0.00

CURRENT CHARGES
{plan_name}:            ${plan_cost:.2f}
Equipment Rental:       ${equipment_fee:.2f}
  - WiFi Router
Taxes & Fees:          ${taxes:.2f}

ADDITIONAL SERVICES
  None

========================
TOTAL AMOUNT DUE:      ${total:.2f}
========================

DUE DATE: {(datetime.now() + timedelta(days=25)).strftime('%B %d, %Y')}

Payment Options:
- Online: www.fastnet.com/pay
- Phone: 1-800-PAY-NET
- Auto Pay: Save $5/month

Support: 1-800-FASTNET
Visit: www.fastnet.com

Account #: {random.randint(100000000, 999999999)}
Bill Date: {datetime.now().strftime('%B %d, %Y')}"""
        
        return bill
    
    def create_comprehensive_sample_data(self):
        """Create a comprehensive set of sample data for testing"""
        print("🚀 Creating Comprehensive Sample Data for Couples Dashboard & Agent Testing")
        print("=" * 80)
        
        # Create multiple bank statements
        self.create_bank_statement_csv("couples_checking_august_2024.csv", 30)
        self.create_bank_statement_csv("couples_savings_july_2024.csv", 20)
        self.create_bank_statement_csv("alex_personal_august_2024.csv", 25)
        self.create_bank_statement_csv("sam_personal_august_2024.csv", 25)
        
        # Create various receipts
        for i in range(5):
            self.create_receipt_text(f"grocery_receipt_{i+1}.txt", "grocery")
        
        for i in range(3):
            self.create_receipt_text(f"restaurant_receipt_{i+1}.txt", "restaurant")
        
        for i in range(2):
            self.create_receipt_text(f"gas_receipt_{i+1}.txt", "gas")
        
        # Create utility bills
        self.create_utility_bill("electric_bill_august_2024.txt", "electric")
        self.create_utility_bill("gas_bill_august_2024.txt", "gas")
        self.create_utility_bill("water_bill_august_2024.txt", "water")
        self.create_utility_bill("internet_bill_august_2024.txt", "internet")
        
        # Create summary report
        self.create_summary_report()
        
        print("\n✅ Sample data creation complete!")
        print("📁 Files created in watch-folders/ directory")
        print("🎯 Ready for watch folder processing and agent testing")
    
    def create_summary_report(self):
        """Create a summary of all created files"""
        summary = {
            "created_timestamp": datetime.now().isoformat(),
            "purpose": "Couples Dashboard & Agentic AI Testing",
            "file_categories": {
                "bank_statements": [],
                "receipts": [],
                "utility_bills": []
            },
            "partners": self.partners,
            "categories": list(self.categories.keys()),
            "testing_scenarios": [
                "Multi-partner expense tracking",
                "Category-based transaction analysis", 
                "Receipt OCR and processing",
                "Utility bill parsing",
                "Agentic transaction categorization",
                "Anomaly detection testing",
                "Pattern recognition validation"
            ]
        }
        
        # Count files in each directory
        for category in ["bank-statements", "receipts", "documents"]:
            path = self.base_path / category
            if path.exists():
                files = list(path.glob("*"))
                summary["file_categories"][category.replace("-", "_")] = [f.name for f in files]
        
        # Write summary
        summary_path = self.base_path / "sample_data_summary.json"
        with open(summary_path, 'w') as f:
            json.dump(summary, f, indent=2)
        
        print(f"✅ Created summary report: {summary_path}")

if __name__ == "__main__":
    creator = SampleDataCreator()
    creator.create_comprehensive_sample_data()