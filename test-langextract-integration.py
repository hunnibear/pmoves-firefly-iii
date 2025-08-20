#!/usr/bin/env python3
"""
Test LangExtract integration with existing project configuration
"""

import sys
import os
import json
from pathlib import Path

# Add project root to path for imports
project_root = Path(__file__).parent
sys.path.insert(0, str(project_root))

def test_langextract_with_config():
    """Test LangExtract using project configuration"""
    try:
        import langextract as lx
        print("✓ LangExtract imported successfully")
        
        # Test basic extraction functionality
        sample_receipt = """
        WHOLE FOODS MARKET
        123 Main St, Anytown USA
        
        Date: 08/19/2025  Time: 2:30 PM
        
        Organic Bananas       $3.49
        Almond Milk 1L        $4.99
        
        Subtotal:            $8.48
        Tax (8.5%):          $0.72
        Total:               $9.20
        
        Payment: VISA ****1234
        """
        
        # Use configuration matching our LangExtractService.php
        print("Testing LangExtract with sample receipt...")
        
        # Define examples for receipt processing
        examples = [
            lx.data.ExampleData(
                text="""WHOLE FOODS MARKET
123 Main St, Anytown USA
Date: 03/15/2024  Time: 2:30 PM
Organic Bananas       $3.49
Almond Milk 1L        $4.99
Subtotal:            $8.48
Tax (8.5%):          $0.72
Total:               $9.20
Payment: VISA ****1234""",
                extractions=[
                    lx.data.Extraction(
                        extraction_class="merchant",
                        extraction_text="WHOLE FOODS MARKET",
                        attributes={"type": "store_name"}
                    ),
                    lx.data.Extraction(
                        extraction_class="total",
                        extraction_text="$9.20",
                        attributes={"amount": "9.20", "type": "final_amount"}
                    ),
                    lx.data.Extraction(
                        extraction_class="date",
                        extraction_text="03/15/2024",
                        attributes={"type": "transaction_date"}
                    ),
                    lx.data.Extraction(
                        extraction_class="item",
                        extraction_text="Organic Bananas",
                        attributes={"price": "3.49", "category": "produce"}
                    ),
                    lx.data.Extraction(
                        extraction_class="item",
                        extraction_text="Almond Milk 1L",
                        attributes={"price": "4.99", "category": "dairy_alternative"}
                    )
                ]
            )
        ]
        
        result = lx.extract(
            text_or_documents=sample_receipt,
            prompt_description="Extract receipt information including merchant, amount, date, items",
            examples=examples,
            language_model_type=lx.inference.OllamaLanguageModel,
            model_id="gemma3:4b",
            model_url="http://localhost:11434",
            extraction_passes=2,
            max_char_buffer=2000,
            fence_output=False,
            use_schema_constraints=False
        )
        
        print(f"✓ Extraction successful! Found {len(result.extractions)} extractions")
        
        # Display extractions
        for i, extraction in enumerate(result.extractions[:5]):  # Show first 5
            print(f"  {i+1}. {extraction.extraction_class}: {extraction.extraction_text}")
            if extraction.attributes:
                print(f"     Attributes: {extraction.attributes}")
        
        return True
        
    except Exception as e:
        print(f"✗ LangExtract test failed: {e}")
        return False

def test_ollama_connection():
    """Test Ollama connectivity"""
    try:
        import requests
        
        # Test connection
        response = requests.get('http://localhost:11434/api/version', timeout=5)
        if response.status_code == 200:
            version_info = response.json()
            print(f"✓ Ollama connected (version: {version_info.get('version', 'unknown')})")
            
            # Check available models
            models_response = requests.get('http://localhost:11434/api/tags', timeout=5)
            if models_response.status_code == 200:
                models = models_response.json().get('models', [])
                print(f"✓ Available models: {len(models)}")
                for model in models:
                    print(f"  - {model.get('name', 'unknown')}")
                
                # Check for our configured model
                model_names = [m.get('name', '') for m in models]
                if 'gemma3:4b' in model_names:
                    print("✓ Test model 'gemma3:4b' is available")
                    return True
                else:
                    print("⚠ Test model 'gemma3:4b' not yet available")
                    return False
            else:
                print("✗ Failed to get model list")
                return False
        else:
            print(f"✗ Ollama connection failed: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"✗ Ollama connection error: {e}")
        return False

def main():
    """Run all tests"""
    print("=== LangExtract Integration Test ===\n")
    
    ollama_ok = test_ollama_connection()
    print()
    
    if ollama_ok:
        langextract_ok = test_langextract_with_config()
        print()
        
        if langextract_ok:
            print("=== All Tests Passed ===")
            print("✓ LangExtract integration is working correctly")
            print("✓ Ready to process receipts and documents")
            return 0
        else:
            print("=== Some Tests Failed ===")
            print("✗ LangExtract integration needs attention")
            return 1
    else:
        print("=== Tests Skipped ===")
        print("⚠ Ollama not ready - model may still be downloading")
        print("Run test again after model download completes")
        return 1

if __name__ == "__main__":
    sys.exit(main())