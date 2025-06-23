This is a Laravel-based API that allows uploading a sales invoice with
one or more invoice lines, and then "forwards" this invoice to Exact Online.

## 🏗️ System design

The application follows a layered architecture pattern with clear separation of concerns, implementing SOLID principles and dependency injection for maintainability and testability.

### 🏛️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                       │
├─────────────────────────────────────────────────────────────┤
│  Controllers (SalesInvoiceController)                       │
│  Requests (UploadInvoiceRequest)                            │
│  OpenAPI Documentation                                      │
├─────────────────────────────────────────────────────────────┤
│                    Business Logic Layer                     │
├─────────────────────────────────────────────────────────────┤
│  Services (InvoiceService, InvoiceLineService)              │
│  Factories (SalesInvoiceFactory)                            │
│  External Services (ExactOnlineService)                     │
├─────────────────────────────────────────────────────────────┤
│                    Data Access Layer                        │
├─────────────────────────────────────────────────────────────┤
│  Models (SalesInvoice, InvoiceLine)                         │
│  Database Migrations                                        │
├─────────────────────────────────────────────────────────────┤
│                    Infrastructure Layer                     │
├─────────────────────────────────────────────────────────────┤
│  External API Client (ExactOnlineFakeClient)                │
│  Dependency Injection Container                             │
└─────────────────────────────────────────────────────────────┘
```

### 🔧 Core Components

#### 1. **Controllers Layer**
**SalesInvoiceController**: 
- Handles HTTP requests for invoice uploads
- Implements OpenAPI documentation for API specification
- Validates incoming requests using Form Request classes
- Delegates business logic to service layer

#### 2. **Business Logic Layer**
- **InvoiceService**: Orchestrates the invoice creation and external API communication
- **InvoiceLineService**: Manages invoice line item operations
- **SalesInvoiceFactory**: Creates invoice entities following factory pattern
- **ExactOnlineService**: Handles communication with external ExactOnline API

#### 3. **Data Models**
- **SalesInvoice**: Main invoice entity with customer details and total amount
- **InvoiceLine**: Individual line items with description, quantity, unit price, and amount
- Implements Eloquent relationships (one-to-many between invoice and lines)

#### 4. **Contracts & Interfaces**
The application uses dependency injection with interfaces to ensure loose coupling:
- `ExactOnlineServiceInterface`: Defines contract for external API communication
- `InvoiceLineServiceInterface`: Defines contract for line item operations
- `SalesInvoiceFactoryInterface`: Defines contract for invoice creation
- `ExternalApiFakeClientInterface`: Defines contract for HTTP client operations

### 🔄 ExactOnline Service Simulation

The ExactOnline integration is simulated using a sophisticated fake client that mimics real-world API behavior:

#### **ExactOnlineFakeClient Implementation**
```php
class ExactOnlineFakeClient implements ExternalApiFakeClientInterface
{
    const SUCCESS_RATE = 0.7; // 70% success rate
    
    public function post(string $endpoint, array $payload): array
    {
        // Simulates API failures and network issues
        if (mt_rand(0, 100) / 100 < self::SUCCESS_RATE) {
            return ['status' => 'success', 'message' => 'Invoice sent to ExactOnline'];
        }
        return ['status' => 'error', 'message' => 'Failed to send invoice to ExactOnline'];
    }
}
```

#### **Key Simulation Features**
1. **Realistic Failure Simulation**: 30% failure rate mimics real-world API unreliability
2. **Payload Transformation**: Converts internal snake_case format to ExactOnline's camelCase format
3. **Logging**: Tracks all API interactions for debugging and monitoring

#### **Payload Transformation Process**
The service transforms internal data structure to ExactOnline format:
```php
// Internal format (snake_case)
[
    'customer_name' => 'Test Customer',
    'invoice_date' => '2024-01-15',
    'total_amount' => 1500.00,
    'invoice_lines' => [...]
]

// ExactOnline format (camelCase)
[
    'CustomerName' => 'Test Customer',
    'InvoiceDate' => '2024-01-15', 
    'TotalAmount' => 1500.00,
    'Lines' => [...]
]
```

### 📊 Data Flow

1. **Request Reception**: API receives POST request with invoice data
2. **Validation**: Form request validates input data and business rules
3. **Database Transaction**: Invoice and line items are created atomically
4. **External API Call**: Invoice data is sent to ExactOnline service
5. **Response Handling**: Success/failure status is returned to client
6. **Logging**: All operations are logged for audit and debugging

### ⚠️ Error Handling Strategy

The system implements a robust error handling approach:
- **Validation Errors**: Return 422 status with detailed validation messages
- **Database Errors**: Rollback transactions on failure
- **External API Errors**: Log failures and return appropriate status
- **System Errors**: Catch exceptions and return 500 status with error details

### 🧪 Testing Strategy

The application includes comprehensive testing:
- **Feature Tests**: End-to-end API testing with database assertions
- **Unit Tests**: Individual service and component testing
- **Simulation Testing**: Fake client allows testing without external dependencies

## 🚀 How to run the service
1. Clone the repository
2. Run `make up` in the **root folder of the repository**. There will be 3 docker containers up:
    - The api server named `laravel-app`
    - The database container named `laravel-db`
    - The swagger UI container named `swagger-ui`

## 🧪 How to test
After the 3 containers are up:
1. Run all the test cases, including unit test and feature test cases: `make test`
2. The swagger UI is available in http://localhost:8080/. You can see complete api documentation, and test the api there.

## 🔧 Simplified modules
There are some modules that usually should be part of the codebase. But for time being, they are just ignored or simplified. Here are some of them that I would like to improve if more time is available.

### 🔐 Authentication
Apparently  you don't want everyone to be able to use the api and inject data to the database, so an authentication is needed. The simplest way is bearer token authentication. For one user session, a token is generated and it should be included in the header of the request. Based on the token, and probably the roles of users, we can also assign different access to different users.

### 🌐 Api client
There is a ExternalApiFakeClientInterface. This interface and its implementations are fake clients used for simulating external API calls. They provide a consistent interface for testing API integrations without making actual HTTP requests.

 In production environments, you would typically:
 - Install and use official client packages (e.g., Guzzle, HTTP Client)
 - Use SDKs provided by the external API vendor
 - Implement real HTTP clients with proper authentication, retry logic, etc.

### 🔄 Handling the failure of sending to ExactOnline

Sending invoice data to ExactOnline can fail. There are different ways to handle it, depending on the purpose of sending the data:

1. Retry the send invoice operation in limited times, say at most 3 times. If it still fails, we should send an email to the user with the invoice details. This method can be used when we want to use the ExactOnline cloud service to send the invoice data with our customer.

2. Retry the send invoice operation in limited times. If it still fails, we should roll back the invoice data stored in the app database;

    OR, we can first create the Eloquent object of invoice and invoice lines, only when the response is successful,we can then store the invoice and invoice lines in the database.

    This method can be used when the synchronization is strictly required, e.g. when making the backup of the invoice data.

3. We can also just log the invoice id when it still fails after the max try times. Later the list of invoice ids that failed to send to ExactOnline can be processed by a cron job regularly. This method fit for the case that the synchronization is not strictly required, and it is also not urgent to do.

Given that we're just simulating the implementation, we choose the simplest method, i.e. just log the invoice id.
