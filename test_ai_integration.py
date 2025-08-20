#!/usr/bin/env python3

import sys
import os
import requests
import json
import tempfile
import textwrap

# Add the project directory to Python path
project_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, project_dir)

def test_ollama_connection():
    """Test if Ollama is accessible"""
    try:
        response = requests.get('http://localhost:11434/api/version', timeout=5)
        if response.status_code == 200:
            version_info = response.json()
            print(f"✅ Ollama is running - Version: {version_info['version']}")
            return True
        else:
            print(f"❌ Ollama responded with status: {response.status_code}")
            return False
    except requests.exceptions.RequestException as e:
        print(f"❌ Cannot connect to Ollama: {e}")
        return False

def test_langextract_import():
    """Test if LangExtract can be imported"""
    try:
        import langextract as lx
        print("✅ LangExtract imported successfully")
        return True
    except ImportError as e:
        print(f"❌ Cannot import LangExtract: {e}")
        return False

def test_financial_receipt_processing():
    """Test LangExtract with a financial receipt example"""
    try:
        import langextract as lx
        
        # Sample receipt text
        receipt_text = textwrap.dedent("""
            TARGET STORE #1234
            123 Shopping Center Drive
            Anytown, CA 90210
            (555) 123-4567
            
            Date: 08/19/2025    Time: 3:45 PM
            REF# 6789-0123-4567
            
            GROCERY ITEMS:
            Organic Apples 3lb        $5.99
            Whole Milk 1 Gallon       $3.79
            Sourdough Bread           $2.49
            Free Range Eggs           $4.29
            
            HOUSEHOLD:
            Laundry Detergent         $8.99
            Paper Towels 6pk          $12.49
            
            Subtotal:                $38.04
            Tax (8.75%):             $3.33
            Total:                   $41.37
            
            Payment: VISA ****2468
            Thank you for shopping!
        """)
        
        # Define prompt for financial receipt processing
        prompt = textwrap.dedent("""
            Extract financial information from receipts including merchant details, transaction amounts, 
            items purchased, payment information, and tax details.
            
            Use exact text for extractions. Do not paraphrase or overlap entities.
            Extract entities in order of appearance.
            Provide meaningful attributes for each entity to add context.
        """)
        
        # Define example for few-shot learning
        examples = [
            lx.data.ExampleData(
                text="WALMART Store #5678\n123 Main St\nBread $2.99\nMilk $3.49\nSubtotal: $6.48\nTax: $0.52\nTotal: $7.00\nVISA ****1234",
                extractions=[
                    lx.data.Extraction(
                        extraction_class="merchant",
                        extraction_text="WALMART Store #5678",
                        attributes={"type": "store_name"}
                    ),
                    lx.data.Extraction(
                        extraction_class="item",
                        extraction_text="Bread",
                        attributes={"price": "2.99", "category": "grocery"}
                    ),
                    lx.data.Extraction(
                        extraction_class="item", 
                        extraction_text="Milk",
                        attributes={"price": "3.49", "category": "grocery"}
                    ),
                    lx.data.Extraction(
                        extraction_class="total",
                        extraction_text="$7.00",
                        attributes={"amount": "7.00", "type": "final_amount"}
                    )
                ]
            )
        ]
        
        # Process with LangExtract using Ollama
        result = lx.extract(
            text_or_documents=receipt_text,
            prompt_description=prompt,
            examples=examples,
            model_id="gemma3:4b",
            model_url="http://localhost:11434",
            fence_output=False,
            use_schema_constraints=False
        )
        
        print("✅ Financial receipt processing test successful!")
        print(f"   Extracted {len(result.extractions)} entities")
        
        # Show some key extractions
        for extraction in result.extractions[:5]:  # Show first 5
            print(f"   - {extraction.extraction_class}: {extraction.extraction_text}")
        
        return True
        
    except Exception as e:
        print(f"❌ Financial receipt processing test failed: {e}")
        return False

def test_ai_categorization():
    """Test AI categorization with a simple example"""
    try:
        prompt = """Analyze this transaction for a couple and categorize it:

Transaction Details:
- Merchant: Whole Foods Market
- Amount: $87.50
- Items: Organic vegetables, bread, milk, eggs

Couple Profile:
- John and Sarah
- Typically shared categories: Groceries, Utilities, Rent, Insurance

Please respond with JSON containing:
1. category: The most appropriate category
2. subcategory: More specific classification if applicable
3. confidence: Confidence score (0-1)
4. reasoning: Brief explanation for the categorization"""

        response = requests.post(
            'http://localhost:11434/api/generate',
            json={
                'model': 'gemma3:4b',
                'prompt': prompt,
                'stream': False,
                'format': 'json'
            },
            timeout=30
        )
        
        if response.status_code == 200:
            result = response.json()
            ai_response = result.get('response', '{}')
            try:
                categorization = json.loads(ai_response)
                print("✅ AI Categorization test successful:")
                print(f"   Category: {categorization.get('category', 'Unknown')}")
                print(f"   Confidence: {categorization.get('confidence', 0)}")
                return True
            except json.JSONDecodeError:
                print(f"⚠️  AI responded but JSON parsing failed. Raw response: {ai_response[:100]}...")
                return False
        else:
            print(f"❌ AI categorization failed with status: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ AI categorization test failed: {e}")
        return False

def test_couples_assignment_suggestion():
    """Test partner assignment suggestion functionality"""
    try:
        prompt = """Suggest who should be assigned this expense in a couple's budget:

Transaction:
- Merchant: Nike Store
- Amount: $120.00
- Category: Clothing
- Items: Running shoes

Couple: Alex and Jordan

Assignment options:
- partner1: Only Alex's responsibility
- partner2: Only Jordan's responsibility  
- shared: Split between both partners

Please respond with JSON containing:
1. assignment: 'partner1', 'partner2', or 'shared'
2. split_percentage: If shared, suggest split (e.g., 50/50, 70/30)
3. confidence: Confidence score (0-1)
4. reasoning: Brief explanation for the assignment"""

        response = requests.post(
            'http://localhost:11434/api/generate',
            json={
                'model': 'gemma3:4b',
                'prompt': prompt,
                'stream': False,
                'format': 'json'
            },
            timeout=30
        )
        
        if response.status_code == 200:
            result = response.json()
            ai_response = result.get('response', '{}')
            try:
                assignment = json.loads(ai_response)
                print("✅ Partner assignment test successful:")
                print(f"   Assignment: {assignment.get('assignment', 'Unknown')}")
                print(f"   Reasoning: {assignment.get('reasoning', 'No reasoning provided')}")
                return True
            except json.JSONDecodeError:
                print(f"⚠️  AI responded but JSON parsing failed. Raw response: {ai_response[:100]}...")
                return False
        else:
            print(f"❌ Partner assignment failed with status: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ Partner assignment test failed: {e}")
        return False

def main():
    print("🔍 Testing Enhanced LangExtract Financial Data Processing")
    print("=" * 60)
    
    all_tests_passed = True
    
    # Test 1: Ollama connection
    print("\n1. Testing Ollama connection...")
    if not test_ollama_connection():
        all_tests_passed = False
    
    # Test 2: LangExtract import
    print("\n2. Testing LangExtract import...")
    if not test_langextract_import():
        all_tests_passed = False
    
    # Test 3: Financial receipt processing
    print("\n3. Testing financial receipt processing...")
    if not test_financial_receipt_processing():
        all_tests_passed = False
    
    # Test 4: AI categorization
    print("\n4. Testing AI categorization...")
    if not test_ai_categorization():
        all_tests_passed = False
    
    # Test 5: Partner assignment suggestions
    print("\n5. Testing partner assignment suggestions...")
    if not test_couples_assignment_suggestion():
        all_tests_passed = False
    
    print("\n" + "=" * 60)
    if all_tests_passed:
        print("🎉 All tests passed! Your enhanced AI integration is ready.")
        print("\nNext steps:")
        print("1. Upload a receipt to: http://localhost:8080/couples/dashboard")
        print("2. Test the receipt processing functionality")
        print("3. Check the AI categorization and partner assignment suggestions")
        print("4. Try uploading a bank statement for transaction analysis")
        print("\nAdvanced features now available:")
        print("- Multi-pass extraction for improved accuracy")
        print("- Comprehensive financial document processing")
        print("- Couples-specific AI categorization")
        print("- Smart partner assignment suggestions")
    else:
        print("❌ Some tests failed. Please check the configuration.")
        print("\nTroubleshooting:")
        print("- Ensure Ollama is running: ollama serve")
        print("- Ensure gemma3:4b model is available: ollama pull gemma3:4b")
        print("- Check Python environment and LangExtract installation")
        print("- Verify requests package is installed: pip install requests")

if __name__ == "__main__":
    main()