#!/usr/bin/env python3
"""Test script to verify LangExtract installation and Ollama connectivity."""

import sys
import json
import traceback

def test_imports():
    """Test importing required libraries."""
    try:
        import langextract
        print("✓ LangExtract imported successfully")
        return True
    except ImportError as e:
        print(f"✗ Failed to import LangExtract: {e}")
        return False

def test_ollama_connection():
    """Test connection to Ollama service."""
    try:
        import requests
        response = requests.get('http://ollama:11434/api/version', timeout=10)
        if response.status_code == 200:
            version_info = response.json()
            print(f"✓ Ollama connection successful - Version: {version_info.get('version', 'unknown')}")
            return True
        else:
            print(f"✗ Ollama connection failed - Status: {response.status_code}")
            return False
    except Exception as e:
        print(f"✗ Ollama connection error: {e}")
        return False

def test_model_availability():
    """Test if required models are available."""
    try:
        import requests
        response = requests.get('http://ollama:11434/api/tags', timeout=10)
        if response.status_code == 200:
            models = response.json()
            model_names = [model['name'] for model in models.get('models', [])]
            
            required_models = ['gemma3:12b', 'gemma3:270m']
            available_models = []
            
            for model in required_models:
                if any(model in name for name in model_names):
                    available_models.append(model)
                    print(f"✓ Model {model} is available")
                else:
                    print(f"✗ Model {model} is not available")
            
            return len(available_models) > 0
        else:
            print(f"✗ Failed to get model list - Status: {response.status_code}")
            return False
    except Exception as e:
        print(f"✗ Model availability check error: {e}")
        return False

def main():
    """Run all tests."""
    print("=== AI Environment Test ===")
    
    all_passed = True
    
    print("\n1. Testing imports...")
    all_passed &= test_imports()
    
    print("\n2. Testing Ollama connection...")
    all_passed &= test_ollama_connection()
    
    print("\n3. Testing model availability...")
    all_passed &= test_model_availability()
    
    print(f"\n=== Test Results ===")
    if all_passed:
        print("✓ All tests passed! AI environment is ready.")
        sys.exit(0)
    else:
        print("✗ Some tests failed. Check the logs above.")
        sys.exit(1)

if __name__ == "__main__":
    main()