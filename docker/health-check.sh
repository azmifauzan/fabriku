#!/bin/bash

# Health check script for Docker container
# This script verifies that essential Laravel components are working

echo "Running health checks..."

# Check if storage link exists and is properly configured
if [ -L "public/storage" ]; then
    echo "✓ Storage link exists"
    
    # Test if the link is working by checking if we can access the target
    if [ -d "$(readlink public/storage)" ]; then
        echo "✓ Storage link is functional"
    else
        echo "✗ Storage link is broken"
        exit 1
    fi
else
    echo "✗ Storage link not found"
    echo "Attempting to create storage link..."
    php artisan storage:link
    
    if [ $? -eq 0 ]; then
        echo "✓ Storage link created successfully"
    else
        echo "✗ Failed to create storage link"
        exit 1
    fi
fi

# Check if storage directories have proper permissions
if [ -w "storage/app/public" ]; then
    echo "✓ Storage directory is writable"
else
    echo "✗ Storage directory is not writable"
    exit 1
fi

# Check if the application responds to HTTP requests
if curl -f http://localhost/health > /dev/null 2>&1; then
    echo "✓ Application is responding to HTTP requests"
else
    echo "✗ Application is not responding"
    exit 1
fi

echo "All health checks passed!"
exit 0
