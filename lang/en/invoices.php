<?php

return [
    'title' => 'Invoices',
    'invoice' => 'Invoice',
    'generate_invoice' => 'Generate Invoice',
    'generate_bulk' => 'Generate Bulk Invoices',
    'invoice_details' => 'Invoice Details',

    // Fields
    'invoice_number' => 'Invoice Number',
    'billing_month' => 'Billing Month',
    'rent_amount' => 'Rent Amount',
    'previous_due' => 'Previous Due',
    'discount' => 'Discount',
    'late_fee' => 'Late Fee',
    'total_amount' => 'Total Amount',
    'paid_amount' => 'Paid Amount',
    'due_amount' => 'Due Amount',
    'status' => 'Status',
    'due_date' => 'Due Date',
    'generated_at' => 'Generated At',
    'shop' => 'Shop',
    'notes' => 'Notes',

    // Status
    'statuses' => [
        'pending' => 'Pending',
        'partial' => 'Partial',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
    ],

    // Messages
    'created' => 'Invoice created successfully',
    'updated' => 'Invoice updated successfully',
    'deleted' => 'Invoice deleted successfully',
    'no_invoices' => 'No invoices found',
    'already_exists' => 'Invoice already exists for this month',
    'bulk_created' => ':count invoices generated successfully',
    'send_reminder' => 'Send Reminder',
    'print_invoice' => 'Print Invoice',
    'download_pdf' => 'Download PDF',
];
