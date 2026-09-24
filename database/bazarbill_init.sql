-- BazarBill — MySQL schema + base data for cPanel
-- Generated 2026-09-23 18:33 from Laravel migrations (includes SMS credits, plans, subscriptions).
-- Import into an EMPTY database via phpMyAdmin. Contains: all tables, migrations ledger,
-- roles/permissions, 3 subscription plans, super admin login admin@bazarbill.com / password.
-- After import, future updates are applied with: php artisan migrate --force
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
/*M!999999\- enable the sandbox mode */ 
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_market_id_created_at_index` (`market_id`,`created_at`),
  KEY `audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `audit_logs_model_type_model_id_index` (`model_type`,`model_id`),
  CONSTRAINT `audit_logs_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `complaints` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint(20) unsigned NOT NULL,
  `shop_id` bigint(20) unsigned NOT NULL,
  `submitted_by` bigint(20) unsigned NOT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `resolution_notes` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complaints_submitted_by_foreign` (`submitted_by`),
  KEY `complaints_assigned_to_foreign` (`assigned_to`),
  KEY `complaints_market_id_status_index` (`market_id`,`status`),
  KEY `complaints_shop_id_status_index` (`shop_id`,`status`),
  CONSTRAINT `complaints_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `complaints_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `complaints_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `complaints_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint(20) unsigned NOT NULL,
  `shop_id` bigint(20) unsigned NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `billing_month` varchar(7) NOT NULL,
  `rent_amount` decimal(12,2) NOT NULL,
  `previous_due` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `late_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','partial','paid','overdue') NOT NULL DEFAULT 'pending',
  `due_date` date NOT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `last_reminder_at` timestamp NULL DEFAULT NULL,
  `reminder_count` smallint(5) unsigned NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_market_id_invoice_number_unique` (`market_id`,`invoice_number`),
  UNIQUE KEY `invoices_shop_id_billing_month_unique` (`shop_id`,`billing_month`),
  KEY `invoices_market_id_billing_month_index` (`market_id`,`billing_month`),
  KEY `invoices_market_id_status_index` (`market_id`,`status`),
  CONSTRAINT `invoices_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `markets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `name_bn` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `address_bn` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `sms_api_key` varchar(255) DEFAULT NULL,
  `sms_sender_id` varchar(255) DEFAULT NULL,
  `sms_templates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sms_templates`)),
  `sms_credits` int(10) unsigned NOT NULL DEFAULT 0,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `plan_id` bigint(20) unsigned DEFAULT NULL,
  `subscription_status` varchar(20) DEFAULT NULL,
  `subscription_ends_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `markets_slug_unique` (`slug`),
  KEY `markets_plan_id_foreign` (`plan_id`),
  CONSTRAINT `markets_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `title_bn` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `content_bn` text DEFAULT NULL,
  `target_role` enum('all','shop_owner','collector') NOT NULL DEFAULT 'all',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notices_created_by_foreign` (`created_by`),
  KEY `notices_market_id_is_pinned_created_at_index` (`market_id`,`is_pinned`,`created_at`),
  CONSTRAINT `notices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notices_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `market_id` bigint(20) unsigned NOT NULL,
  `shop_id` bigint(20) unsigned NOT NULL,
  `collected_by` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(20) NOT NULL DEFAULT 'cash',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `receipt_number` varchar(255) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_collected_by_foreign` (`collected_by`),
  KEY `payments_market_id_payment_date_index` (`market_id`,`payment_date`),
  KEY `payments_shop_id_payment_date_index` (`shop_id`,`payment_date`),
  CONSTRAINT `payments_collected_by_foreign` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `name_bn` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `monthly_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `yearly_price` decimal(10,2) DEFAULT NULL,
  `shop_limit` int(10) unsigned DEFAULT NULL,
  `sms_credits_per_month` int(10) unsigned NOT NULL DEFAULT 0,
  `trial_days` int(10) unsigned NOT NULL DEFAULT 14,
  `trial_sms_credits` int(10) unsigned NOT NULL DEFAULT 20,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shops` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint(20) unsigned NOT NULL,
  `shop_owner_id` bigint(20) unsigned DEFAULT NULL,
  `collector_id` bigint(20) unsigned DEFAULT NULL,
  `shop_number` varchar(255) NOT NULL,
  `floor` varchar(255) DEFAULT NULL,
  `area_sqft` decimal(10,2) DEFAULT NULL,
  `rent_amount` decimal(12,2) NOT NULL,
  `advance_deposit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shop_type` enum('general','food','clothing','electronics','jewelry','pharmacy','other') NOT NULL DEFAULT 'general',
  `status` enum('active','vacant','suspended') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shops_market_id_shop_number_unique` (`market_id`,`shop_number`),
  KEY `shops_shop_owner_id_foreign` (`shop_owner_id`),
  KEY `shops_collector_id_foreign` (`collector_id`),
  CONSTRAINT `shops_collector_id_foreign` FOREIGN KEY (`collector_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shops_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shops_shop_owner_id_foreign` FOREIGN KEY (`shop_owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_credit_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint(20) unsigned NOT NULL,
  `type` varchar(20) NOT NULL,
  `amount` int(11) NOT NULL,
  `balance_after` int(10) unsigned NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `sms_log_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_credit_transactions_created_by_foreign` (`created_by`),
  KEY `sms_credit_transactions_sms_log_id_foreign` (`sms_log_id`),
  KEY `sms_credit_transactions_market_id_created_at_index` (`market_id`,`created_at`),
  KEY `sms_credit_transactions_market_id_type_index` (`market_id`,`type`),
  CONSTRAINT `sms_credit_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sms_credit_transactions_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sms_credit_transactions_sms_log_id_foreign` FOREIGN KEY (`sms_log_id`) REFERENCES `sms_logs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint(20) unsigned NOT NULL,
  `recipient_phone` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `gateway` varchar(20) NOT NULL DEFAULT 'platform',
  `credits_used` smallint(5) unsigned NOT NULL DEFAULT 0,
  `api_response` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_logs_market_id_status_index` (`market_id`,`status`),
  KEY `sms_logs_market_id_created_at_index` (`market_id`,`created_at`),
  CONSTRAINT `sms_logs_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint(20) unsigned NOT NULL,
  `plan_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL,
  `billing_cycle` varchar(10) NOT NULL DEFAULT 'monthly',
  `starts_at` date NOT NULL,
  `ends_at` date NOT NULL,
  `next_sms_allocation_at` date DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(20) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_plan_id_foreign` (`plan_id`),
  KEY `subscriptions_created_by_foreign` (`created_by`),
  KEY `subscriptions_market_id_status_index` (`market_id`,`status`),
  KEY `subscriptions_status_ends_at_index` (`status`,`ends_at`),
  CONSTRAINT `subscriptions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subscriptions_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `name_bn` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` enum('super_admin','market_owner','collector','shop_owner') NOT NULL DEFAULT 'shop_owner',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `language_preference` enum('en','bn') NOT NULL DEFAULT 'bn',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_market_id_foreign` (`market_id`),
  CONSTRAINT `users_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

-- ---------------------------------------------------------------- base data
/*M!999999\- enable the sandbox mode */ 
set autocommit=0;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'0001_01_01_000003_create_markets_table',1),
(5,'2026_09_05_161131_create_permission_tables',1),
(6,'2026_09_05_161641_add_market_fields_to_users_table',1),
(7,'2026_09_05_161702_create_shops_table',1),
(8,'2026_09_05_161703_create_invoices_table',1),
(9,'2026_09_05_161704_create_payments_table',1),
(10,'2026_09_05_161705_create_complaints_table',1),
(11,'2026_09_05_161706_create_notices_table',1),
(12,'2026_09_05_161707_create_sms_logs_table',1),
(13,'2026_09_05_161708_create_audit_logs_table',1),
(14,'2026_09_17_155111_add_method_details_to_payments_table',1),
(15,'2026_09_23_000001_add_sms_credits_to_markets_table',1),
(16,'2026_09_23_000002_create_sms_credit_transactions_table',1),
(17,'2026_09_23_000003_create_plans_table',1),
(18,'2026_09_23_000004_create_subscriptions_table',1),
(19,'2026_09_23_000005_add_reminder_tracking_to_invoices_table',1);
commit;
set autocommit=0;
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (1,'manage markets','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(2,'view shops','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(3,'create shops','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(4,'edit shops','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(5,'delete shops','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(6,'view staff','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(7,'create staff','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(8,'edit staff','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(9,'delete staff','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(10,'view invoices','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(11,'create invoices','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(12,'edit invoices','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(13,'delete invoices','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(14,'view payments','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(15,'collect payments','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(16,'delete payments','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(17,'view reports','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(18,'export reports','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(19,'view complaints','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(20,'create complaints','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(21,'manage complaints','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(22,'view notices','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(23,'create notices','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(24,'edit notices','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(25,'delete notices','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(26,'manage settings','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(27,'send sms','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(28,'view sms logs','web','2026-09-23 06:33:59','2026-09-23 06:33:59');
commit;
set autocommit=0;
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (1,'super_admin','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(2,'market_owner','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(3,'collector','web','2026-09-23 06:33:59','2026-09-23 06:33:59'),
(4,'shop_owner','web','2026-09-23 06:33:59','2026-09-23 06:33:59');
commit;
set autocommit=0;
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (1,1),
(2,1),
(3,1),
(4,1),
(5,1),
(6,1),
(7,1),
(8,1),
(9,1),
(10,1),
(11,1),
(12,1),
(13,1),
(14,1),
(15,1),
(16,1),
(17,1),
(18,1),
(19,1),
(20,1),
(21,1),
(22,1),
(23,1),
(24,1),
(25,1),
(26,1),
(27,1),
(28,1),
(2,2),
(3,2),
(4,2),
(5,2),
(6,2),
(7,2),
(8,2),
(9,2),
(10,2),
(11,2),
(12,2),
(13,2),
(14,2),
(15,2),
(16,2),
(17,2),
(18,2),
(19,2),
(21,2),
(22,2),
(23,2),
(24,2),
(25,2),
(26,2),
(27,2),
(28,2),
(2,3),
(3,3),
(10,3),
(14,3),
(15,3),
(19,3),
(21,3),
(22,3),
(10,4),
(14,4),
(19,4),
(20,4),
(22,4);
commit;
set autocommit=0;
INSERT INTO `plans` (`id`, `name`, `name_bn`, `slug`, `description`, `monthly_price`, `yearly_price`, `shop_limit`, `sms_credits_per_month`, `trial_days`, `trial_sms_credits`, `features`, `is_default`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (1,'Starter','স্টার্টার','starter','For small markets and single buildings.',1000.00,10000.00,50,200,14,20,'{\"masking_sms\":false,\"pdf_reports\":true,\"complaints\":true,\"notices\":true}',1,1,1,'2026-09-23 06:33:59','2026-09-23 06:33:59'),
(2,'Standard','স্ট্যান্ডার্ড','standard','For mid-size markets with several collectors.',2500.00,25000.00,200,1000,14,20,'{\"masking_sms\":true,\"pdf_reports\":true,\"complaints\":true,\"notices\":true}',0,1,2,'2026-09-23 06:33:59','2026-09-23 06:33:59'),
(3,'Enterprise','এন্টারপ্রাইজ','enterprise','Unlimited shops for large markets and market committees.',5000.00,50000.00,NULL,3000,14,20,'{\"masking_sms\":true,\"pdf_reports\":true,\"complaints\":true,\"notices\":true}',0,1,3,'2026-09-23 06:33:59','2026-09-23 06:33:59');
commit;
set autocommit=0;
INSERT INTO `users` (`id`, `market_id`, `name`, `name_bn`, `email`, `phone`, `role`, `avatar`, `is_active`, `language_preference`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (1,NULL,'Super Admin','সুপার এডমিন','admin@bazarbill.com','01800000000','super_admin',NULL,1,'en',NULL,'$2y$12$zt8sDUBZXb8zIYqS21ckzuKPp.oV1aGWG4lt6agE1ATCFbwK.1cjS',NULL,'2026-09-23 06:33:59','2026-09-23 06:33:59');
commit;
set autocommit=0;
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES (1,'App\\Models\\User',1);
commit;
SET FOREIGN_KEY_CHECKS=1;
