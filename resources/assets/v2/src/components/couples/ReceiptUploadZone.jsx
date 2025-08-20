import React, { useState, useCallback, useRef } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Switch } from '@/components/ui/switch';
import { 
  Upload, 
  Camera, 
  FileText, 
  CheckCircle, 
  AlertCircle, 
  Loader2,
  Image as ImageIcon,
  CreditCard,
  Receipt,
  Smartphone,
  Eye
} from 'lucide-react';

const DocumentUploadZone = ({ onUploadSuccess, onUploadError }) => {
  const [uploading, setUploading] = useState(false);
  const [dragOver, setDragOver] = useState(false);
  const [progress, setProgress] = useState(0);
  const [result, setResult] = useState(null);
  const [error, setError] = useState(null);
  const [uploadMode, setUploadMode] = useState('receipt');
  const [createTransaction, setCreateTransaction] = useState(true);
  const [previewUrl, setPreviewUrl] = useState(null);
  const fileInputRef = useRef(null);
  const cameraInputRef = useRef(null);

  // Document type configurations
  const documentTypes = {
    receipt: {
      title: 'Receipt Processing',
      description: 'Upload receipts, invoices, or purchase confirmations',
      icon: Receipt,
      accept: 'image/*,application/pdf,.txt',
      maxSize: '25MB',
      examples: 'Store receipts, restaurant bills, invoices, purchase confirmations'
    },
    statement: {
      title: 'Bank Statement Processing', 
      description: 'Upload bank statements or transaction exports',
      icon: CreditCard,
      accept: 'image/*,application/pdf,.csv,.txt,.xlsx,.xls',
      maxSize: '50MB',
      examples: 'Bank statements, credit card statements, CSV exports, Excel files'
    },
    photo: {
      title: 'Photo Capture',
      description: 'Take photos with your phone camera',
      icon: Smartphone,
      accept: 'image/*',
      maxSize: '25MB',
      examples: 'Phone camera photos, screenshots, scanned documents'
    },
    document: {
      title: 'General Document',
      description: 'Upload any financial document for AI analysis',
      icon: FileText,
      accept: 'image/*,application/pdf,.txt,.csv,.xlsx,.xls',
      maxSize: '50MB',
      examples: 'Any financial document, transaction records, account summaries'
    }
  };

  const currentType = documentTypes[uploadMode];

  const handleDragOver = useCallback((e) => {
    e.preventDefault();
    setDragOver(true);
  }, []);

  const handleDragLeave = useCallback((e) => {
    e.preventDefault();
    setDragOver(false);
  }, []);

  const handleDrop = useCallback((e) => {
    e.preventDefault();
    setDragOver(false);
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
      handleFileUpload(files[0]);
    }
  }, []);

  const handleFileSelect = useCallback((e) => {
    const file = e.target.files[0];
    if (file) {
      handleFileUpload(file);
    }
  }, []);

  const handleCameraCapture = useCallback(() => {
    if (cameraInputRef.current) {
      cameraInputRef.current.click();
    }
  }, []);

  const validateFile = (file) => {
    const currentType = documentTypes[uploadMode];
    
    // Check file type
    const acceptedTypes = currentType.accept.split(',').map(type => type.trim());
    const isValidType = acceptedTypes.some(type => {
      if (type === 'image/*') return file.type.startsWith('image/');
      if (type === 'application/pdf') return file.type === 'application/pdf';
      if (type === '.txt') return file.name.toLowerCase().endsWith('.txt');
      if (type === '.csv') return file.name.toLowerCase().endsWith('.csv');
      if (type === '.xlsx') return file.name.toLowerCase().endsWith('.xlsx');
      if (type === '.xls') return file.name.toLowerCase().endsWith('.xls');
      return file.type === type;
    });

    if (!isValidType) {
      throw new Error(`Please upload a valid file type. Accepted: ${currentType.accept}`);
    }

    // Check file size
    const maxSizeBytes = currentType.maxSize === '25MB' ? 25 * 1024 * 1024 : 50 * 1024 * 1024;
    if (file.size > maxSizeBytes) {
      throw new Error(`File size must be less than ${currentType.maxSize}.`);
    }

    return true;
  };

  const createFilePreview = (file) => {
    if (file.type.startsWith('image/')) {
      const url = URL.createObjectURL(file);
      setPreviewUrl(url);
      return () => URL.revokeObjectURL(url);
    }
    setPreviewUrl(null);
    return null;
  };

  const handleFileUpload = async (file) => {
    try {
      validateFile(file);
      
      setUploading(true);
      setError(null);
      setResult(null);
      setProgress(0);

      // Create preview for images
      const cleanupPreview = createFilePreview(file);

      // Simulate progress for better UX
      const progressInterval = setInterval(() => {
        setProgress(prev => prev < 80 ? prev + 10 : prev);
      }, 300);

      // Create FormData for file upload
      const formData = new FormData();
      formData.append('document', file);
      formData.append('document_type', uploadMode);
      formData.append('create_transaction', createTransaction);
      formData.append('use_vision_model', file.type.startsWith('image/'));

      // Get CSRF token from meta tag (standard Laravel approach)
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      // Determine the correct endpoint based on upload mode
      const endpoint = uploadMode === 'statement' 
        ? '/api/v1/couples/process-bank-statement'
        : '/api/v1/couples/upload-receipt';

      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
      });

      clearInterval(progressInterval);
      setProgress(100);

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Upload failed');
      }

      if (data.status === 'success') {
        setResult(data);
        onUploadSuccess?.(data);
        
        // Cleanup preview after success
        if (cleanupPreview) {
          setTimeout(cleanupPreview, 5000);
        }
      } else {
        throw new Error(data.message || 'Processing failed');
      }

    } catch (err) {
      console.error('Document upload error:', err);
      setError(err.message || 'Failed to process document. Please try again.');
      onUploadError?.(err);
      setPreviewUrl(null);
    } finally {
      setUploading(false);
      setTimeout(() => setProgress(0), 2000); // Reset progress after 2 seconds
    }
  };

  const resetUpload = () => {
    setResult(null);
    setError(null);
    setProgress(0);
    setPreviewUrl(null);
    if (fileInputRef.current) fileInputRef.current.value = '';
    if (cameraInputRef.current) cameraInputRef.current.value = '';
  };

  const getProcessingMessage = () => {
    switch (uploadMode) {
      case 'receipt':
        return 'AI is extracting merchant, amount, date, and items...';
      case 'statement':
        return 'AI is processing transactions and categorizing them...';
      case 'photo':
        return 'Vision model is analyzing the photo content...';
      case 'document':
        return 'AI is analyzing document content and extracting data...';
      default:
        return 'Processing document...';
    }
  };

  return (
    <Card className="w-full max-w-4xl mx-auto">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <currentType.icon className="h-5 w-5" />
          AI Document Processing
        </CardTitle>
        <CardDescription>
          Upload receipts, bank statements, or photos for AI-powered transaction extraction
        </CardDescription>
      </CardHeader>
      
      <CardContent className="space-y-6">
        {/* Document Type Selection */}
        <Tabs value={uploadMode} onValueChange={setUploadMode} className="w-full">
          <TabsList className="grid w-full grid-cols-4">
            <TabsTrigger value="receipt" className="flex flex-col gap-1 py-3">
              <Receipt className="h-4 w-4" />
              <span className="text-xs">Receipt</span>
            </TabsTrigger>
            <TabsTrigger value="statement" className="flex flex-col gap-1 py-3">
              <CreditCard className="h-4 w-4" />
              <span className="text-xs">Statement</span>
            </TabsTrigger>
            <TabsTrigger value="photo" className="flex flex-col gap-1 py-3">
              <Smartphone className="h-4 w-4" />
              <span className="text-xs">Photo</span>
            </TabsTrigger>
            <TabsTrigger value="document" className="flex flex-col gap-1 py-3">
              <FileText className="h-4 w-4" />
              <span className="text-xs">Document</span>
            </TabsTrigger>
          </TabsList>

          {Object.entries(documentTypes).map(([key, type]) => (
            <TabsContent key={key} value={key} className="space-y-4">
              <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 className="font-medium text-blue-900 mb-2">{type.title}</h3>
                <p className="text-sm text-blue-700 mb-2">{type.description}</p>
                <p className="text-xs text-blue-600">
                  <strong>Examples:</strong> {type.examples}
                </p>
                <p className="text-xs text-blue-600 mt-1">
                  <strong>Accepted formats:</strong> {type.accept} (max {type.maxSize})
                </p>
              </div>
            </TabsContent>
          ))}
        </Tabs>

        {/* Upload Options */}
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <Switch
              id="create-transaction"
              checked={createTransaction}
              onCheckedChange={setCreateTransaction}
            />
            <Label htmlFor="create-transaction" className="text-sm">
              Automatically create transaction
            </Label>
          </div>
        </div>

        {/* Upload Zone */}
        <div
          className={`border-2 border-dashed rounded-lg p-8 text-center transition-colors ${
            dragOver 
              ? 'border-primary bg-primary/5' 
              : uploading 
                ? 'border-blue-300 bg-blue-50' 
                : 'border-gray-300 hover:border-gray-400'
          }`}
          onDragOver={handleDragOver}
          onDragLeave={handleDragLeave}
          onDrop={handleDrop}
        >
          {uploading ? (
            <div className="space-y-4">
              <Loader2 className="h-8 w-8 animate-spin mx-auto text-blue-500" />
              <div className="space-y-2">
                <p className="text-sm font-medium">Processing {uploadMode}...</p>
                <Progress value={progress} className="w-full" />
                <p className="text-xs text-muted-foreground">
                  {getProcessingMessage()}
                </p>
              </div>
            </div>
          ) : (
            <div className="space-y-4">
              <currentType.icon className="h-12 w-12 mx-auto text-gray-400" />
              <div className="space-y-2">
                <p className="text-lg font-medium">Drop your {uploadMode} here</p>
                <p className="text-sm text-muted-foreground">
                  or use the buttons below to browse or capture
                </p>
              </div>
              
              <div className="flex flex-wrap gap-2 justify-center">
                <Label htmlFor="file-upload" className="cursor-pointer">
                  <Input
                    ref={fileInputRef}
                    id="file-upload"
                    type="file"
                    accept={currentType.accept}
                    onChange={handleFileSelect}
                    className="hidden"
                    disabled={uploading}
                  />
                  <Button variant="outline" disabled={uploading}>
                    <Upload className="h-4 w-4 mr-2" />
                    Browse Files
                  </Button>
                </Label>
                
                {(uploadMode === 'photo' || uploadMode === 'receipt') && (
                  <Label htmlFor="camera-capture" className="cursor-pointer">
                    <Input
                      ref={cameraInputRef}
                      id="camera-capture"
                      type="file"
                      accept="image/*"
                      capture="environment"
                      onChange={handleFileSelect}
                      className="hidden"
                      disabled={uploading}
                    />
                    <Button variant="outline" disabled={uploading}>
                      <Camera className="h-4 w-4 mr-2" />
                      Take Photo
                    </Button>
                  </Label>
                )}
              </div>
              
              <p className="text-xs text-muted-foreground">
                Max size: {currentType.maxSize} • Formats: {currentType.accept}
              </p>
            </div>
          )}
        </div>

        {/* File Preview */}
        {previewUrl && (
          <Card className="border-green-200 bg-green-50">
            <CardContent className="p-4">
              <div className="flex items-center gap-4">
                <img 
                  src={previewUrl} 
                  alt="Preview" 
                  className="w-20 h-20 object-cover rounded border"
                />
                <div>
                  <p className="text-sm font-medium text-green-800">File ready for processing</p>
                  <p className="text-xs text-green-600">Preview available</p>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Error Display */}
        {error && (
          <Alert variant="destructive">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}

        {/* Success Result */}
        {result && (
          <div className="space-y-4">
            <Alert className="border-green-200 bg-green-50">
              <CheckCircle className="h-4 w-4 text-green-600" />
              <AlertDescription className="text-green-800">
                {uploadMode === 'statement' ? (
                  <>
                    Bank statement processed successfully! Found{' '}
                    <strong>{result.extracted_data?.transaction_count || 0} transactions</strong>
                  </>
                ) : (
                  <>
                    {uploadMode.charAt(0).toUpperCase() + uploadMode.slice(1)} processed successfully! Found{' '}
                    <strong>{result.extracted_data?.merchant_name || 'merchant'}</strong>{' '}
                    for <strong>${result.extracted_data?.total_amount || '0.00'}</strong>
                  </>
                )}
              </AlertDescription>
            </Alert>

            {/* Enhanced Extraction Results */}
            <Card>
              <CardHeader>
                <CardTitle className="text-sm flex items-center gap-2">
                  <Eye className="h-4 w-4" />
                  AI Extraction Results
                  {result.extracted_data?.processing_metadata?.model_used && (
                    <Badge variant="outline" className="text-xs">
                      {result.extracted_data.processing_metadata.model_used}
                    </Badge>
                  )}
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {uploadMode === 'statement' ? (
                  // Bank Statement Results
                  <div className="space-y-3">
                    <div className="grid grid-cols-2 gap-4 text-sm">
                      <div>
                        <Label className="text-xs font-medium text-muted-foreground">
                          Total Transactions
                        </Label>
                        <p className="font-medium">
                          {result.extracted_data?.transaction_count || 0}
                        </p>
                      </div>
                      <div>
                        <Label className="text-xs font-medium text-muted-foreground">
                          Date Range
                        </Label>
                        <p className="font-medium">
                          {result.extracted_data?.date_range || 'Not specified'}
                        </p>
                      </div>
                    </div>
                    
                    {result.extracted_data?.transactions && (
                      <div className="border-t pt-3">
                        <Label className="text-xs font-medium text-muted-foreground mb-2 block">
                          Sample Transactions
                        </Label>
                        <div className="space-y-2 max-h-40 overflow-y-auto">
                          {result.extracted_data.transactions.slice(0, 5).map((tx, index) => (
                            <div key={index} className="text-xs bg-gray-50 p-2 rounded">
                              <div className="flex justify-between">
                                <span className="font-medium">{tx.description}</span>
                                <span className={tx.amount < 0 ? 'text-red-600' : 'text-green-600'}>
                                  ${Math.abs(tx.amount).toFixed(2)}
                                </span>
                              </div>
                              <div className="text-gray-500">{tx.date}</div>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}
                  </div>
                ) : (
                  // Receipt/Document Results  
                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <Label className="text-xs font-medium text-muted-foreground">
                        Merchant
                      </Label>
                      <p className="font-medium">
                        {result.extracted_data?.merchant_name || 'Unknown'}
                      </p>
                    </div>
                    <div>
                      <Label className="text-xs font-medium text-muted-foreground">
                        Amount
                      </Label>
                      <p className="font-medium">
                        ${result.extracted_data?.total_amount || '0.00'}
                      </p>
                    </div>
                    <div>
                      <Label className="text-xs font-medium text-muted-foreground">
                        Date
                      </Label>
                      <p className="font-medium">
                        {result.extracted_data?.date || 'Today'}
                      </p>
                    </div>
                    <div>
                      <Label className="text-xs font-medium text-muted-foreground">
                        Confidence
                      </Label>
                      <div className="flex items-center gap-2">
                        <Badge variant={
                          result.ai_suggestions?.confidence > 0.8 
                            ? 'default' 
                            : result.ai_suggestions?.confidence > 0.6 
                              ? 'secondary' 
                              : 'outline'
                        }>
                          {Math.round((result.ai_suggestions?.confidence || 0) * 100)}%
                        </Badge>
                      </div>
                    </div>
                  </div>
                )}

                {/* AI Suggestions */}
                {result.ai_suggestions && (
                  <div className="border-t pt-3 space-y-2">
                    <Label className="text-xs font-medium text-muted-foreground">
                      AI Suggestions
                    </Label>
                    <div className="flex flex-wrap gap-2">
                      <Badge variant="outline">
                        {result.ai_suggestions.category || 'Uncategorized'}
                      </Badge>
                      <Badge variant="outline">
                        {result.ai_suggestions.partner_assignment || 'Shared'}
                      </Badge>
                      {result.ai_suggestions.payment_method && (
                        <Badge variant="outline">
                          {result.ai_suggestions.payment_method}
                        </Badge>
                      )}
                    </div>
                  </div>
                )}

                {/* Processing Metadata */}
                <div className="border-t pt-3 text-xs text-muted-foreground">
                  <p>
                    Processed in {result.processing_time} using{' '}
                    {result.extracted_data?.processing_metadata?.model_used || 'AI model'}
                    {result.extracted_data?.processing_metadata?.vision_model && (
                      <span> + {result.extracted_data.processing_metadata.vision_model}</span>
                    )}
                  </p>
                </div>
              </CardContent>
            </Card>

            {/* Action Buttons */}
            <div className="flex gap-2">
              <Button onClick={resetUpload} variant="outline" size="sm">
                Upload Another
              </Button>
              {!createTransaction && (
                <Button size="sm">
                  Create Transaction
                </Button>
              )}
              {result.extracted_data?.transactions && uploadMode === 'statement' && (
                <Button size="sm" variant="secondary">
                  Review {result.extracted_data.transactions.length} Transactions
                </Button>
              )}
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
};

export default DocumentUploadZone;