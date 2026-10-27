-- ==============================================================================
-- Proper Computer — Institute Management System & Public Website Database Schema
-- Database: proper_computer_db
-- Target: MySQL / MariaDB (XAMPP Compatible)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `proper_computer_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `proper_computer_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. Roles Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Administrator', 'Full system access and configurations'),
(2, 'Center Head', 'Branch and center operations manager'),
(3, 'Counselor', 'Student admissions and enquiry follow-ups'),
(4, 'Instructor', 'Course delivery and student attendance'),
(5, 'Accountant', 'Fee collection, payments, and financial reports');

-- -----------------------------------------------------------------------------
-- 2. Permissions Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
  `permission_key` VARCHAR(100) NOT NULL,
  KEY (`role_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `permissions` (`role_id`, `permission_key`) VALUES
(1, 'all_access'),
(2, 'manage_admissions'), (2, 'manage_enquiries'), (2, 'manage_fees'), (2, 'view_reports'), (2, 'manage_employees'),
(3, 'manage_enquiries'), (3, 'manage_admissions'), (3, 'view_receipts'),
(4, 'view_students'), (4, 'mark_attendance'), (4, 'view_courses'),
(5, 'manage_fees'), (5, 'view_receipts'), (5, 'view_reports'), (5, 'manage_budget');

-- -----------------------------------------------------------------------------
-- 3. Users Table (Staff & Admins)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `role_id` INT NOT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `last_login` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin User: admin / admin123
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `role_id`, `status`) VALUES
(1, 'admin', 'admin@propercomputer.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFhQ8h0r25u7k1n7U9KGyO5ZqmN1qO2C', 'Tuba Zainab', '9822334455', 1, 'active'),
(2, 'counselor', 'counselor@propercomputer.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFhQ8h0r25u7k1n7U9KGyO5ZqmN1qO2C', 'Ravina Saini', '9876543211', 3, 'active'),
(3, 'accountant', 'accounts@propercomputer.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFhQ8h0r25u7k1n7U9KGyO5ZqmN1qO2C', 'Sanjay Saini', '9876543212', 5, 'active');

-- -----------------------------------------------------------------------------
-- 4. Course Categories Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `course_categories`;
CREATE TABLE `course_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'fas fa-laptop-code',
  `description` TEXT NULL,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `course_categories` (`id`, `name`, `slug`, `icon`, `description`, `sort_order`) VALUES
(1, 'Programming and Web Development', 'programming-web', 'fas fa-code', 'Full stack, modern frameworks, backend architecture and algorithms', 1),
(2, 'Data and Artificial Intelligence', 'data-ai', 'fas fa-brain', 'Machine learning, data analytics, LLMs, GenAI tools and BI suites', 2),
(3, 'Cybersecurity and IT Infrastructure', 'cybersecurity-it', 'fas fa-shield-alt', 'Ethical hacking, SOC analyst, cloud architecture, and DevOps', 3),
(4, 'Design, Video Editing and Creative Technology', 'design-creative', 'fas fa-paint-brush', 'UI/UX design, motion graphics, 3D game dev, VFX, and video production', 4),
(5, 'Digital Marketing and Content Creation', 'digital-marketing', 'fas fa-chart-line', 'Performance marketing, SEO, social growth, and brand building', 5),
(6, 'Finance, Business and Management', 'finance-business', 'fas fa-chart-pie', 'Financial modelling, valuation, fundamental analysis, and HR management', 6),
(7, 'Blockchain and Emerging Technologies', 'blockchain-emerging', 'fas fa-cubes', 'Decentralized systems, smart contracts, Web3, and cryptography', 7);

-- -----------------------------------------------------------------------------
-- 5. Courses Table (All 65+ unique courses)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `code` VARCHAR(30) NOT NULL UNIQUE,
  `duration` VARCHAR(50) NOT NULL DEFAULT '3 Months',
  `fee` DECIMAL(10,2) NOT NULL DEFAULT 15000.00,
  `level` ENUM('Beginner', 'Intermediate', 'Advanced', 'All Levels') DEFAULT 'Beginner',
  `mode` ENUM('Online', 'Offline', 'Hybrid') DEFAULT 'Hybrid',
  `description` TEXT NULL,
  `syllabus` LONGTEXT NULL,
  `is_popular` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `courses` (`category_id`, `name`, `slug`, `code`, `duration`, `fee`, `level`, `mode`, `description`, `syllabus`, `is_popular`) VALUES
-- 1. Programming and Web Development
(1, 'Web Development with PHP/MySQL', 'web-development-with-php-mysql', 'PC-DEV-01', '3 Months', 15000.00, 'Beginner', 'Hybrid', 'Master dynamic web applications, OOP PHP, PDO database interactions, REST APIs, session management and secure authentication.', 'HTML5 & CSS3 Essentials\nJavaScript & AJAX Basics\nCore & Advanced PHP 8\nMySQL Database Design & PDO\nCRUD Operations & State Management\nSecurity: CSRF, XSS, Password Hashing\nCapstone Real-World Project', 1),
(1, 'MERN Stack', 'mern-stack', 'PC-DEV-02', '4 Months', 22000.00, 'Intermediate', 'Hybrid', 'Comprehensive full-stack development with MongoDB, Express.js, React.js, and Node.js for modern web apps.', 'Modern JavaScript (ES6+)\nReact.js State Management & Hooks\nNode.js & Express.js REST APIs\nMongoDB Schema Design with Mongoose\nJWT Authentication & Authorization\nDeployment on Vercel & AWS', 1),
(1, 'React JS', 'react-js', 'PC-DEV-03', '2 Months', 14000.00, 'Beginner', 'Online', 'Build high-performance, reactive single-page front-end web applications with React components, hooks, and Redux Toolkit.', 'Component Architecture\nProps, State & Lifecycle\nHooks (useState, useEffect, useMemo)\nRouting with React Router 6\nRedux Toolkit & Global State\nAPI Integration & Tailwind CSS', 1),
(1, 'Next Js', 'next-js', 'PC-DEV-04', '2.5 Months', 18000.00, 'Intermediate', 'Hybrid', 'Master server-side rendering (SSR), static site generation (SSG), app router, API handlers and SEO optimization with Next.js.', 'Next.js App Router Architecture\nServer & Client Components\nServer Actions & Data Fetching\nNextAuth.js Authentication\nSEO Optimization & Image Handling\nProduction Deployment', 0),
(1, 'Nestjs', 'nestjs', 'PC-DEV-05', '2 Months', 18000.00, 'Advanced', 'Online', 'Enterprise-grade, scalable server-side TypeScript backend architecture with NestJS, dependency injection, and microservices.', 'TypeScript Fundamentals\nNestJS Modules, Controllers & Services\nTypeORM & Prisma Database Integration\nGuards, Interceptors & Pipes\nMicroservices & WebSockets\nSwagger API Documentation', 0),
(1, 'Java', 'java', 'PC-DEV-06', '3 Months', 15000.00, 'Beginner', 'Hybrid', 'Core to Advanced Java programming covering OOP concepts, collections framework, multithreading, and JDBC.', 'Java Syntax & Data Types\nObject-Oriented Programming (OOP)\nException Handling & File I/O\nJava Collections Framework\nMultithreading & Concurrency\nJDBC & MySQL Integration', 1),
(1, 'C++', 'cpp', 'PC-DEV-07', '2.5 Months', 12000.00, 'Beginner', 'Offline', 'High-performance programming language covering memory pointers, object-oriented concepts, and STL.', 'C++ Syntax & Control Flow\nPointers, References & Memory Management\nOOP in C++ (Inheritance, Polymorphism)\nStandard Template Library (STL)\nFile Handling & Streams', 0),
(1, 'Python', 'python', 'PC-DEV-08', '2.5 Months', 14000.00, 'Beginner', 'Hybrid', 'Comprehensive Python programming from fundamental syntax to file automation, web scraping, and OOP design.', 'Python Syntax, Variables & Collections\nFunctions & Functional Tools\nOOP in Python\nModules, Packages & Pip\nFile Handling & Automation Scripts\nIntro to Data Analysis & APIs', 1),
(1, 'DSA with Java', 'dsa-with-java', 'PC-DEV-09', '4 Months', 20000.00, 'Intermediate', 'Hybrid', 'Algorithmic problem solving and data structures implemented in Java for competitive programming and technical interviews.', 'Time & Space Complexity (Big-O)\nArrays, Linked Lists, Stacks & Queues\nTrees, Binary Search Trees & Heaps\nGraphs & Traversal (BFS, DFS)\nDynamic Programming & Recursion\nInterview Question Patterns', 1),
(1, 'DSA with Python', 'dsa-with-python', 'PC-DEV-10', '4 Months', 20000.00, 'Intermediate', 'Hybrid', 'Comprehensive Data Structures and Algorithms implemented purely in Python for technical coding rounds.', 'Algorithmic Complexity & Python Profiling\nLists, Tuples, Dictionaries, Sets\nCustom Stacks, Queues, Deques\nBinary Trees & Balanced Trees\nGreedy Algorithms & Backtracking\nLeetCode Top 150 Problems', 0),
(1, 'Data Structures & Algorithms', 'data-structures-algorithms', 'PC-DEV-11', '3.5 Months', 18000.00, 'Intermediate', 'Hybrid', 'Language-agnostic core data structures, theoretical foundations, computational thinking, and complex algorithms.', 'Foundations of Algorithm Analysis\nLinear Data Structures\nNon-Linear Data Structures\nSorting & Searching Algorithms\nDivide and Conquer Paradigms\nGraph Theory & Shortest Path', 0),
(1, 'Competitive Programming', 'competitive-programming', 'PC-DEV-12', '3 Months', 16000.00, 'Advanced', 'Online', 'Advanced contest programming, number theory, bit manipulation, range queries, and dynamic programming.', 'Contest Strategies & Fast I/O\nNumber Theory & Combinatorics\nBit Manipulation & Segment Trees\nAdvanced DP & Tree DP\nDisjoint Set Union (DSU)\nCodeforces & LeetCode Hard Patterns', 0),
(1, 'System Design', 'system-design', 'PC-DEV-13', '2.5 Months', 22000.00, 'Advanced', 'Online', 'Architect large-scale distributed systems, microservices, caching layers, load balancers, and database sharding.', 'Vertical vs Horizontal Scaling\nLoad Balancing & Reverse Proxies\nCaching Strategies (Redis/Memcached)\nDatabase Sharding & Replication\nMessage Queues (Kafka, RabbitMQ)\nCase Studies: YouTube, Uber, WhatsApp', 0),
(1, 'Database Management System', 'database-management-system', 'PC-DEV-14', '2 Months', 12000.00, 'Beginner', 'Hybrid', 'Relational database theory, ER modelling, ACID properties, indexing, normalization, and relational schema optimization.', 'Database Architecture & ER Models\nRelational Algebra & Normalization\nTransactions & Concurrency Control\nIndexes, B-Trees & Query Optimization\nNoSQL Concepts vs RDBMS', 0),
(1, 'SQL', 'sql', 'PC-DEV-15', '1.5 Months', 8000.00, 'Beginner', 'Hybrid', 'Master structured query language: complex joins, nested subqueries, window functions, views, and stored procedures.', 'SELECT, WHERE, ORDER BY, GROUP BY\nTable Joins (Inner, Left, Right, Full)\nAggregate Functions & Subqueries\nWindow Functions (ROW_NUMBER, RANK)\nStored Procedures & Triggers', 1),
(1, 'Wordpress', 'wordpress', 'PC-DEV-16', '1.5 Months', 9000.00, 'Beginner', 'Hybrid', 'Build modern corporate websites, WooCommerce e-commerce stores, custom themes and blogs without coding.', 'WordPress Installation & Local Setup\nTheme Customization & Page Builders (Elementor)\nEssential Plugins & Security Setup\nWooCommerce Online Store Configuration\nPayment Gateway Integration\nSpeed Optimization & SEO', 0),
(1, 'App Development', 'app-development', 'PC-DEV-17', '4 Months', 25000.00, 'Intermediate', 'Hybrid', 'Comprehensive mobile application engineering for iOS and Android platforms, APIs, and state architectures.', 'Mobile UX Patterns & Architecture\nNative Device APIs (Camera, GPS, Storage)\nPush Notifications & Background Services\nOffline Caching & State Management\nPublishing to Play Store & App Store', 0),
(1, 'React Native', 'react-native', 'PC-DEV-18', '3 Months', 22000.00, 'Intermediate', 'Hybrid', 'Cross-platform native mobile application development for Android and iOS using JavaScript and React.', 'React Native Core Components & Flexbox\nNavigation with React Navigation\nRedux State Management in Mobile\nNative Device Features & Permissions\nDeployment & Over-the-air Updates', 0),
(1, 'Flutter', 'flutter', 'PC-DEV-19', '3.5 Months', 22000.00, 'Intermediate', 'Hybrid', 'Build beautiful, natively compiled cross-platform apps using Google Dart language and Flutter widget architecture.', 'Dart Language Fundamentals\nFlutter Widget Tree & Custom Layouts\nState Management (Bloc / Provider)\nREST API Integration & Local Storage\nAnimations & Material 3 / Cupertino\nProduction Build for Android & iOS', 1),

-- 2. Data and Artificial Intelligence
(2, 'Data Science', 'data-science', 'PC-DAI-01', '6 Months', 35000.00, 'Intermediate', 'Hybrid', 'End-to-end data science lifecycle: exploratory analysis, machine learning algorithms, deep learning models, and business deployment.', 'Python for Data Science (NumPy, Pandas)\nData Visualization (Matplotlib, Seaborn)\nStatistical Inference & Hypothesis Testing\nMachine Learning Algorithms\nDeep Learning & Neural Networks\nModel Deployment with Flask/Streamlit', 1),
(2, 'Data Analytics', 'data-analytics', 'PC-DAI-02', '3.5 Months', 20000.00, 'Beginner', 'Hybrid', 'Transform raw business data into actionable visual insights using Advanced Excel, SQL, and Power BI dashboards.', 'Advanced Excel & Business Formulas\nSQL for Analytics & Data Extraction\nPower BI Data Modeling & DAX\nInteractive Dashboard Creation\nData Storytelling & Executive Presentations', 1),
(2, 'Business Analytics', 'business-analytics', 'PC-DAI-03', '3 Months', 22000.00, 'Intermediate', 'Hybrid', 'Bridge data insights with executive corporate decisions, predictive analytics, financial metrics, and KPI tracking.', 'Business Problem Framing\nExploratory Data Analysis\nPredictive Modeling & Forecasting\nKPI Dashboards in Tableau\nFinancial & Marketing Metrics Analysis', 0),
(2, 'Data Engineering', 'data-engineering', 'PC-DAI-04', '4.5 Months', 30000.00, 'Advanced', 'Online', 'Build resilient data pipelines, ETL workflows, data warehousing, Apache Spark processing, and cloud data lakes.', 'Data Pipeline Architecture\nSQL & Relational/NoSQL Stores\nApache Spark & PySpark Big Data\nAirflow Pipeline Orchestration\nData Warehousing (Snowflake / BigQuery)', 0),
(2, 'Power BI', 'power-bi', 'PC-DAI-05', '2 Months', 12000.00, 'Beginner', 'Hybrid', 'Master Microsoft Power BI: Power Query, dimensional data modelling, DAX measures, and corporate dashboard publishing.', 'Power Query Data Transformation\nStar Schema & Relational Modeling\nDAX Calculations (CALCULATE, RELATED)\nVisual Reports & Interactive Filters\nPower BI Service & Scheduled Refresh', 1),
(2, 'Tableau', 'tableau', 'PC-DAI-06', '2 Months', 12000.00, 'Beginner', 'Hybrid', 'Interactive visual analytics, calculated fields, LOD expressions, dual-axis charts, and visual dashboards with Tableau.', 'Tableau Desktop Environment\nConnecting to Data Sources & Blending\nCalculations & Level of Detail (LOD)\nInteractive Dashboards & Stories\nPublishing & Server Sharing', 0),
(2, 'Excel', 'excel', 'PC-DAI-07', '1.5 Months', 6000.00, 'Beginner', 'Offline', 'From basic spreadsheets to advanced VLOOKUP/XLOOKUP, PivotTables, conditional formatting, and automation macros.', 'Basic Functions & Formatting\nVLOOKUP, XLOOKUP & INDEX/MATCH\nPivot Tables & Pivot Charts\nData Validation & Conditional Formatting\nWhat-If Analysis & Introduction to Macros', 1),
(2, 'Machine Learning (ML)', 'machine-learning-ml', 'PC-DAI-08', '3.5 Months', 25000.00, 'Intermediate', 'Hybrid', 'Supervised and unsupervised machine learning algorithms, model evaluation metrics, Scikit-Learn, and feature engineering.', 'Data Preprocessing & Feature Engineering\nLinear & Logistic Regression\nDecision Trees, Random Forests & XGBoost\nClustering (K-Means, DBSCAN)\nModel Evaluation & Hyperparameter Tuning', 1),
(2, 'Deep Learning', 'deep-learning', 'PC-DAI-09', '3.5 Months', 28000.00, 'Advanced', 'Online', 'Artificial neural networks, CNNs for computer vision, RNN/LSTMs for time-series, and PyTorch / TensorFlow.', 'Perceptrons & Multi-Layer Networks\nBackpropagation & Gradient Descent\nConvolutional Neural Networks (CNN)\nRecurrent Networks & LSTMs\nTransfer Learning with PyTorch', 0),
(2, 'Natural Language Processing (NLP)', 'natural-language-processing-nlp', 'PC-DAI-10', '3 Months', 26000.00, 'Advanced', 'Online', 'Text analytics, sentiment classification, tokenization, word embeddings (Word2Vec), and Transformer models (BERT).', 'Text Preprocessing & Regex\nN-Gram Models & TF-IDF\nWord Embeddings (Word2Vec, GloVe)\nSequence Models & Attention Mechanism\nTransformers & BERT for NLP', 0),
(2, 'Large Language Model (LLM)', 'large-language-model-llm', 'PC-DAI-11', '2.5 Months', 30000.00, 'Advanced', 'Online', 'Understand LLM architectures, fine-tuning techniques (LoRA/QLoRA), prompt engineering, and building AI applications.', 'Attention Mechanism & Transformers\nPretraining vs Fine-Tuning\nPrompt Engineering & Evaluation\nParameter-Efficient Fine-Tuning (PEFT/LoRA)\nDeploying LLM APIs with Ollama & HuggingFace', 1),
(2, 'Gen AI', 'gen-ai', 'PC-DAI-12', '2 Months', 24000.00, 'Beginner', 'Online', 'Generative AI landscape: multimodal models, LangChain, RAG (Retrieval Augmented Generation), and vector databases.', 'Generative AI Concepts & Modalities\nVector Embeddings & Vector DBs (Chroma/Pinecone)\nRetrieval Augmented Generation (RAG)\nLangChain & LlamaIndex Frameworks\nBuilding Autonomous AI Agents', 1),
(2, 'AI Tools', 'ai-tools', 'PC-DAI-13', '1 Month', 8000.00, 'All Levels', 'Online', 'Boost personal and professional productivity using 50+ cutting-edge generative AI tools for writing, research, and analysis.', 'ChatGPT & Claude Advanced Prompting\nPerplexity & Research Automation\nAI for Productivity (Notion AI, Copilot)\nAI Presentations (Gamma, Beautiful.ai)\nEthical AI Usage & Workflows', 0),
(2, 'AI Video Generation', 'ai-video-generation', 'PC-DAI-14', '1.5 Months', 12000.00, 'Beginner', 'Online', 'Create studio-quality videos, realistic digital avatars, synthetic voiceovers, and marketing reels using modern AI generators.', 'Scriptwriting with AI\nDigital Avatars (HeyGen, Synthesia)\nText-to-Video Generators (Runway Gen-2, Pika)\nAI Voice Cloning with ElevenLabs\nEditing & Finishing in Premiere Pro', 0),

-- 3. Cybersecurity and IT Infrastructure
(3, 'Ethical Hacking', 'ethical-hacking', 'PC-SEC-01', '3.5 Months', 25000.00, 'Intermediate', 'Hybrid', 'Penetration testing methodologies, network reconnaissance, vulnerability assessment, Kali Linux, and exploitation.', 'Footprinting & Reconnaissance\nNetwork Scanning with Nmap & Wireshark\nVulnerability Analysis & Metasploit\nWeb Application Penetration Testing\nWireless Network Attacks & Defense\nReporting & Remediation Standards', 1),
(3, 'Cybersecurity', 'cybersecurity', 'PC-SEC-02', '3 Months', 22000.00, 'Beginner', 'Hybrid', 'Fundamentals of cybersecurity, network security protocols, firewall architectures, identity management, and compliance.', 'Information Security Fundamentals\nCryptography & Symmetric/Asymmetric Ciphers\nNetwork Security, Firewalls & VPNs\nIdentity & Access Management (IAM)\nIncident Response & Compliance (ISO 27001)', 0),
(3, 'SOC Analyst', 'soc-analyst', 'PC-SEC-03', '3 Months', 24000.00, 'Intermediate', 'Online', 'Hands-on Security Operations Center workflows, SIEM analysis, incident detection, alert triage, and threat hunting.', 'SOC Architecture & Tier 1/2 Roles\nSIEM Tools (Splunk, Elastic SIEM)\nLog Analysis & Detection Rules\nPhishing Investigation & Triage\nMITRE ATT&CK Framework Mapping', 0),
(3, 'Linux', 'linux', 'PC-SEC-04', '2 Months', 10000.00, 'Beginner', 'Offline', 'Command-line mastery, user/file permissions, bash scripting, system services, and server administration.', 'Linux File Hierarchy & Navigation\nUser, Group & File Permissions\nProcess Management & Cron Jobs\nNetworking & SSH Configuration\nBash Shell Scripting & Automation', 1),
(3, 'Cloud Computing', 'cloud-computing', 'PC-SEC-05', '2.5 Months', 18000.00, 'Beginner', 'Hybrid', 'Foundational cloud concepts, service models (IaaS, PaaS, SaaS), cloud storage, virtual networking, and security.', 'Introduction to Cloud Computing\nVirtualization & Multi-Tenancy\nCloud Storage (Block, Object, File)\nVirtual Private Cloud & Subnets\nCloud Security & Cost Management', 0),
(3, 'AWS Cloud Practitioner', 'aws-cloud-practitioner', 'PC-SEC-06', '2.5 Months', 18000.00, 'Beginner', 'Hybrid', 'Prepare for AWS CLF-C02 certification: EC2, S3, RDS, IAM, VPC, Lambda, billing and architecture principles.', 'AWS Global Infrastructure\nCompute Services (EC2, Lambda, ECS)\nStorage (S3, EBS, EFS)\nDatabases (RDS, DynamoDB)\nIAM, Security Groups & Billing Management\nCertification Mock Exams', 1),
(3, 'DevOps', 'devops', 'PC-SEC-07', '4 Months', 28000.00, 'Intermediate', 'Hybrid', 'CI/CD pipeline automation, Git workflows, Docker containerization, Kubernetes clusters, Jenkins, and Ansible.', 'DevOps Culture & Git Version Control\nContinuous Integration with Jenkins / GitHub Actions\nDocker Container Architecture\nKubernetes Orchestration & Helm\nConfiguration Management with Ansible\nMonitoring with Prometheus & Grafana', 1),
(3, 'Docker and Kubernetes', 'docker-and-kubernetes', 'PC-SEC-08', '2.5 Months', 20000.00, 'Intermediate', 'Online', 'Containerize applications with Docker, orchestrate microservices with Kubernetes, manage pods, services, and ingress.', 'Docker Containers vs VMs\nDockerfiles, Images & Multi-Stage Builds\nDocker Compose Multi-Container Apps\nKubernetes Architecture & Pods/Deployments\nServices, Ingress & Persistent Volumes', 0),
(3, 'Infrastructure as Code', 'infrastructure-as-code', 'PC-SEC-09', '2 Months', 18000.00, 'Advanced', 'Online', 'Automate cloud provisioning, declare server configurations, and manage infrastructure versions via code.', 'IaC Philosophy & Declarative Models\nConfiguration Management Principles\nCloudFormation & Azure ARM\nIntegrating IaC in CI/CD Pipelines\nDrift Detection & State Locking', 0),
(3, 'Terraform', 'terraform', 'PC-SEC-10', '2 Months', 18000.00, 'Advanced', 'Online', 'Provision multi-cloud infrastructure with HashiCorp Terraform: HCL syntax, state files, modules, and work-spaces.', 'Terraform HCL Syntax & Providers\nState Management & Remote Backends\nReusable Terraform Modules\nVariables, Outputs & Dynamic Blocks\nTerraform Cloud & Production Best Practices', 0),

-- 4. Design, Video Editing and Creative Technology
(4, 'UI/UX', 'ui-ux', 'PC-DES-01', '3 Months', 20000.00, 'Beginner', 'Hybrid', 'Design intuitive, modern digital products: wireframing, interactive prototyping, Figma mastery, and design systems.', 'Design Thinking Process\nUser Personas & Journey Mapping\nWireframing & Information Architecture\nFigma Component Systems & Auto Layout\nInteractive Prototyping & Micro-interactions\nDesign Handoff to Developers', 1),
(4, 'UX Research', 'ux-research', 'PC-DES-02', '2 Months', 15000.00, 'Intermediate', 'Online', 'Qualitative and quantitative research methods, user usability testing, heuristic evaluation, and product validation.', 'Formulating Research Questions\nUser Interviews & Surveys\nCard Sorting & Tree Testing\nUsability Testing & Session Recording\nSynthesizing Insights into Product Roadmaps', 0),
(4, 'Graphic Design', 'graphic-design', 'PC-DES-03', '3 Months', 15000.00, 'Beginner', 'Offline', 'Principles of visual composition, typography, color theory, branding identity, packaging, and commercial printing.', 'Principles of Visual Design & Layout\nTypography, Hierarchy & Font Pairing\nColor Theory & Psychology\nBranding, Logos & Marketing Collateral\nPrint Preparation & Color Profiles (CMYK vs RGB)', 1),
(4, 'Graphic Design With AI', 'graphic-design-with-ai', 'PC-DES-04', '2 Months', 16000.00, 'Beginner', 'Hybrid', 'Supercharge graphic design workflows with Midjourney, Firefly, Stable Diffusion, and automated creative tools.', 'AI Image Generation Foundations\nPrompt Engineering for Commercial Visuals\nAdobe Firefly Generative Fill in Photoshop\nVectorizing AI Artwork in Illustrator\nCreating AI-Assisted Brand Assets', 0),
(4, 'Canva', 'canva', 'PC-DES-05', '1 Month', 5000.00, 'Beginner', 'Offline', 'Fast, stunning visual design for social media posts, presentations, banners, resumes, and print materials with Canva Pro.', 'Canva Workspace & Layout Tools\nSocial Media Post & Story Design\nBusiness Presentations & Pitch Decks\nBrand Kit Setup & Custom Templates\nAnimation & Video Clips in Canva', 1),
(4, 'Adobe Photoshop', 'adobe-photoshop', 'PC-DES-06', '2 Months', 12000.00, 'Beginner', 'Offline', 'Industry-standard raster editing, photo manipulation, retouching, layer masks, compositing, and poster design.', 'Photoshop Workspace, Layers & Selections\nMasking, Smart Objects & Adjustment Layers\nRetouching, Healing & Color Correction\nDigital Compositing & Blending Modes\nExporting for Web and Print', 1),
(4, 'Illustrator', 'illustrator', 'PC-DES-07', '2 Months', 12000.00, 'Beginner', 'Offline', 'Master vector artwork, logos, icons, typography, geometric patterns, and illustrations with Adobe Illustrator.', 'Pen Tool & Vector Path Manipulation\nShape Builder & Pathfinder Operations\nTypography, Outlines & Custom Lettering\nColor Palettes, Gradients & Patterns\nVector Illustration & SVG Export', 0),
(4, 'Adobe InDesign', 'adobe-indesign', 'PC-DES-08', '1.5 Months', 10000.00, 'Beginner', 'Offline', 'Editorial layout design for multi-page brochures, magazines, eBooks, product catalogues, and print publishing.', 'Master Pages, Margins & Grids\nParagraph & Character Styles\nHandling Text Frames & Threading\nImages, Tables & Document Layout\nPreflight & PDF Print Preparation', 0),
(4, 'Affinity Studio', 'affinity-studio', 'PC-DES-09', '2 Months', 12000.00, 'Beginner', 'Offline', 'Complete creative suite with Affinity Designer, Photo, and Publisher as modern professional graphic design tools.', 'Affinity Designer Vector Persona\nAffinity Photo Non-Destructive Editing\nAffinity Publisher Multi-Page Layout\nCross-App StudioLink Integration\nExporting High-Resolution Deliverables', 0),
(4, 'Adobe Premiere Pro', 'adobe-premiere-pro', 'PC-DES-10', '2 Months', 15000.00, 'Beginner', 'Offline', 'Professional video editing, timeline assembly, audio mixing, color grading with Lumetri, and dynamic transitions.', 'Premiere Pro Timeline & Workspace\nAssembly, Rough Cuts & Fine Tuning\nTransitions, Titles & Motion Graphics\nAudio Cleanup, Foley & Mixing\nColor Correction & Lumetri Looks\nRender Optimization & Codecs', 1),
(4, 'AfterEffects', 'after-effects', 'PC-DES-11', '2.5 Months', 18000.00, 'Intermediate', 'Offline', 'Visual effects, motion graphics, keyframe animation, 3D camera tracking, typography animation, and composition.', 'Keyframing & Graph Editor Speed Curves\nShape Layers & Mask Animations\nTypography & Kinetic Motion Graphics\n3D Layers & Camera Tracking\nGreen Screen Keying & Rotoscoping', 0),
(4, 'DaVinci Resolve', 'davinci-resolve', 'PC-DES-12', '2.5 Months', 18000.00, 'Intermediate', 'Offline', 'Hollywood-standard color grading, Fairlight audio mastering, Fusion VFX, and high-end video editing workflows.', 'Edit Page Essentials & Multi-Cam\nColor Grading Node Architecture\nColor Wheels, Curves & Power Windows\nFairlight Audio EQ, Dynamics & Foley\nFusion Page Node Compositing', 0),
(4, 'Video Editing', 'video-editing', 'PC-DES-13', '3 Months', 18000.00, 'Beginner', 'Offline', 'Comprehensive video production fundamentals: pacing, storytelling, sound design, transitions, and YouTube optimization.', 'Grammar of Video Editing & Cut Types\nStoryboarding & Pacing\nSound Effects & Soundtrack Selection\nSocial Media Formats (16:9, 9:16, 1:1)\nExporting for YouTube, Reels & TV', 1),
(4, 'VN Mobile Video Editing', 'vn-mobile-video-editing', 'PC-DES-14', '1 Month', 6000.00, 'Beginner', 'Offline', 'Edit cinematic reels and short-form videos directly from your smartphone using VN Video Editor and CapCut.', 'VN Interface & Mobile Timeline\nMulti-Track Audio & Beat Syncing\nCurve Speed Ramps & Keyframes\nFilters, LUTs & Captions\nExporting Viral Vertical Content', 0),
(4, 'VFX', 'vfx', 'PC-DES-15', '3.5 Months', 25000.00, 'Advanced', 'Offline', 'Cinematic visual effects: matte painting, CGI compositing, particle simulations, explosions, and green screen.', 'VFX Pipeline & Concept Art\nChroma Keying & Garbage Matting\nMatchmoving & Camera Projection\nParticle Systems & Dynamic Simulations\nCGI Integration & Multi-Pass Compositing', 0),
(4, 'Motion Graphics', 'motion-graphics', 'PC-DES-16', '3 Months', 20000.00, 'Intermediate', 'Offline', 'Animated visual designs, brand title sequences, kinetic typography, explainer videos, and logo animations.', 'Design Principles in Motion\nLogo Reveals & Title Sequences\nCharacter Rigging Basics & Walk Cycles\nExplainer Video Scene Transitions\nSound Design for Motion Graphics', 0),
(4, 'Blender', 'blender', 'PC-DES-17', '3.5 Months', 22000.00, 'Intermediate', 'Offline', '3D modelling, UV unwrapping, procedural texturing, character rigging, animation, and Cycles/Eevee photorealistic rendering.', 'Blender Interface & Navigation\n3D Mesh Modeling & Modifiers\nSculpting & Retopology\nMaterials, Shading & UV Unwrapping\nLighting & Cycles Rendering\nCharacter Rigging & Basic Animation', 1),
(4, 'Unreal Engine', 'unreal-engine', 'PC-DES-18', '4 Months', 30000.00, 'Advanced', 'Offline', 'Next-generation real-time 3D environments, virtual production, Blueprints visual scripting, and Lumen/Nanite rendering.', 'Unreal Engine Architecture\nLandscape & World Building\nLumen Global Illumination & Nanite\nBlueprints Visual Scripting\nCinematic Sequencer & Virtual Production\nPackaging Real-Time Applications', 0),
(4, '3D Game Development in Unity', '3d-game-development-in-unity', 'PC-DES-19', '4 Months', 28000.00, 'Intermediate', 'Offline', 'Build complete 3D games with Unity engine, C# scripting, physics engines, UI canvases, and gameplay mechanics.', 'Unity Editor & Game Objects\nC# Scripting for Gameplay Mechanics\nRigidbodies, Colliders & Physics\nAnimation Controllers & Blend Trees\nParticle Systems & Sound Effects\nBuilding & Publishing a Complete 3D Game', 0),

-- 5. Digital Marketing and Content Creation
(5, 'Digital Marketing', 'digital-marketing', 'PC-MKT-01', '3 Months', 16000.00, 'Beginner', 'Hybrid', 'Complete 360-degree online marketing: SEO, social media management, Google Ads, email funnels, and analytics.', 'Digital Marketing Fundamentals & Funnels\nSearch Engine Optimization (SEO)\nSearch Engine Marketing (Google Ads)\nSocial Media Marketing (Meta, LinkedIn)\nEmail Marketing & Automation\nGoogle Analytics 4 (GA4) Tracking', 1),
(5, 'Search Engine Optimization (SEO) Mastery', 'seo-mastery', 'PC-MKT-02', '2 Months', 12000.00, 'Intermediate', 'Online', 'On-page, technical, and off-page SEO strategies to rank top on Google: keyword research, schema markup, and backlinks.', 'Search Engine Algorithms & Crawlers\nAdvanced Keyword & Competitor Research\nOn-Page Optimization & Content Clusters\nTechnical SEO, Core Web Vitals & Schemas\nBacklink Strategies & White-Hat Link Building', 0),
(5, 'Performance Marketing', 'performance-marketing', 'PC-MKT-03', '2.5 Months', 20000.00, 'Intermediate', 'Online', 'Data-driven paid ads on Meta and Google: ROAS optimization, retargeting funnels, pixel tracking, and bid strategies.', 'Paid Advertising Frameworks & KPIs\nMeta Ads Manager (Campaign, Ad Set, Creative)\nGoogle Search, Display & Performance Max\nPixel Tracking, Conversions API & Events\nA/B Testing & Scaling Budgets with High ROAS', 1),
(5, 'Growth Hacking', 'growth-hacking', 'PC-MKT-04', '2 Months', 18000.00, 'Advanced', 'Online', 'Rapid experimentation across product and marketing channels to scale user acquisition, activation, and retention.', 'The Growth Hacking Mindset & AARRR Funnel\nViral Loops & Referral Engine Mechanics\nConversion Rate Optimization (CRO)\nAutomated Scraping & Cold Outreach\nRetention Tactics & Product-Led Growth', 0),
(5, 'Instagram Content Creation', 'instagram-content-creation', 'PC-MKT-05', '1.5 Months', 8000.00, 'Beginner', 'Hybrid', 'Master the Instagram algorithm: viral reels production, carousel design, storytelling captions, and monetization.', 'Understanding Instagram Algorithms\nHook Formulation & Viral Reel Scripts\nLighting, Framing & Mobile Shooting\nCarousel Strategy & Design\nMonetization, Collaborations & Brand Deals', 1),
(5, 'YouTube Content Creation', 'youtube-content-creation', 'PC-MKT-06', '2 Months', 12000.00, 'Beginner', 'Hybrid', 'Launch and scale a profitable YouTube channel: niche research, click-worthy titles/thumbnails, editing, and YouTube SEO.', 'Channel Niche Selection & Audience Persona\nClick-Worthy Title & Thumbnail Psychology\nScripting for Audience Retention\nStudio Lighting & Audio Recording\nYouTube Studio Analytics & Monetization Streams', 0),
(5, 'Copywriting', 'copywriting', 'PC-MKT-07', '1.5 Months', 10000.00, 'Beginner', 'Online', 'High-converting persuasive writing for landing pages, sales letters, ad creatives, email newsletters, and social media.', 'Psychology of Persuasion & Buying Triggers\nHeadlines & The AIDA/PAS Frameworks\nLanding Page Copywriting That Converts\nHigh-Impact Email Newsletters & Subject Lines\nAd Copy for Facebook, Instagram & Google', 0),
(5, 'UGC', 'ugc', 'PC-MKT-08', '1 Month', 8000.00, 'Beginner', 'Online', 'User-Generated Content creation: film authentic product reviews, aesthetic unboxings, and secure paying brand partnerships.', 'What is UGC & Brand Collaboration\nFilming Aesthetics: Unboxing, Demos, Testimonials\nAudio Quality & Captions That Convert\nBuilding a Winning UGC Portfolio & Media Kit\nPitching to D2C Brands & Rate Negotiation', 0),
(5, 'D2C Brand Building', 'd2c-brand-building', 'PC-MKT-09', '2.5 Months', 22000.00, 'Intermediate', 'Online', 'Launch, market, and scale a direct-to-consumer brand: product sourcing, Shopify setup, packaging, and logistics.', 'D2C Market Opportunity & Niche Ideation\nSupplier Sourcing, Quality & Packaging\nShopify Store Build & High-Converting UX\nLogistics, COD Management & RTO Reduction\nScaling with Meta Ads & Influencer Seeding', 0),
(5, 'N8N Automation', 'n8n-automation', 'PC-MKT-10', '2 Months', 15000.00, 'Beginner', 'Online', 'Automate business operations, lead funnels, social media publishing, and AI workflows without writing code using n8n.', 'n8n Workflow Nodes, Triggers & Webhooks\nConnecting CRMs, Google Sheets & Slack\nIntegrating OpenAI & LLM Nodes for AI Automation\nHandling Errors, Cron Triggers & Complex Logic\nSelf-Hosting n8n & Enterprise Deployments', 0),

-- 6. Finance, Business and Management
(6, 'Financial Analysis', 'financial-analysis', 'PC-FIN-01', '3 Months', 20000.00, 'Intermediate', 'Hybrid', 'Analyze corporate balance sheets, P&L statements, cash flows, financial ratios, and variance forecasting.', 'Financial Statements Architecture (P&L, Balance Sheet, Cash Flow)\nRatio Analysis (Liquidity, Solvency, Profitability)\nWorking Capital Management\nVariance Analysis & Budget Forecasting\nFinancial Reporting & Executive Presentations', 1),
(6, 'Financial Modelling & Valuation', 'financial-modelling-valuation', 'PC-FIN-02', '3 Months', 25000.00, 'Advanced', 'Hybrid', 'Build dynamic 3-statement financial models, DCF valuations, and comparable company analysis in Microsoft Excel.', 'Building Dynamic 3-Statement Financial Models\nRevenue & Expense Drivers Modeling\nDiscounted Cash Flow (DCF) Valuation\nComparable Company Analysis (Trading Comps)\nSensitivity Analysis & Scenario Tables', 0),
(6, 'Fundamental Analysis', 'fundamental-analysis', 'PC-FIN-03', '2 Months', 14000.00, 'Beginner', 'Online', 'Evaluate company intrinsic value, competitive economic moats, quarterly earnings reports, and long-term equities.', 'Understanding the Economy, Industry & Company (EIC Framework)\nReading Annual Reports & Footnotes\nValuation Metrics (P/E, P/B, EV/EBITDA, ROE)\nIdentifying Economic Moats & Management Quality\nBuilding a Long-Term Investment Portfolio', 0),
(6, 'Technical Analysis', 'technical-analysis', 'PC-FIN-04', '2 Months', 14000.00, 'Beginner', 'Online', 'Stock market chart analysis: candlestick patterns, support/resistance, RSI, MACD, and risk-managed trading strategies.', 'Candlestick Anatomy & Chart Patterns\nSupport, Resistance & Trendlines\nTechnical Indicators (RSI, MACD, Moving Averages)\nPrice Action & Volume Analysis\nRisk Management, Position Sizing & Stop Loss Rules', 1),
(6, 'Risk Management', 'risk-management', 'PC-FIN-05', '2 Months', 18000.00, 'Intermediate', 'Online', 'Identify, evaluate, and mitigate financial, market, credit, operational, and regulatory enterprise risks.', 'Introduction to Enterprise Risk Management\nMarket Risk & Value-at-Risk (VaR)\nCredit Risk & Counterparty Exposure\nOperational Risk & Disaster Recovery\nRegulatory Frameworks & Basel Accords', 0),
(6, 'HR Management', 'hr-management', 'PC-FIN-06', '2.5 Months', 15000.00, 'Beginner', 'Hybrid', 'Core human resource functions: recruitment pipelines, employee onboarding, payroll compliance, and appraisals.', 'Strategic Human Resource Management\nTalent Acquisition & Interviewing Techniques\nEmployee Onboarding & Training Programs\nPerformance Appraisals (KPIs & OKRs)\nStatutory Compliance, PF, ESI & Labor Laws', 0),

-- 7. Blockchain and Emerging Technologies
(7, 'Blockchain', 'blockchain', 'PC-BLK-01', '3.5 Months', 26000.00, 'Intermediate', 'Hybrid', 'Blockchain architecture: cryptographic hashing, consensus algorithms, peer-to-peer networks, and Ethereum.', 'Cryptographic Foundations & Hash Functions\nDistributed Ledgers & Consensus (PoW, PoS)\nBitcoin Protocol Architecture\nEthereum Virtual Machine (EVM)\nSmart Contracts Basics with Solidity', 1),
(7, 'Web3 Mastery', 'web3-mastery', 'PC-BLK-02', '3 Months', 28000.00, 'Advanced', 'Online', 'Build full-stack decentralized dApps: Solidity smart contracts, Hardhat testing, IPFS storage, and ethers.js frontend.', 'Solidity Advanced Programming & ERC Standards\nHardhat Testing, Debugging & Gas Optimization\nEthers.js / Web3.js Frontend Integration\nDecentralized Storage (IPFS / Arweave)\nSecurity Auditing & Common Vulnerabilities', 0);

-- -----------------------------------------------------------------------------
-- 6. Employees Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'Teacher',
  `designation` VARCHAR(100) NOT NULL,
  `qualification` VARCHAR(100) NULL,
  `skills` VARCHAR(255) NULL,
  `base_salary` DECIMAL(10,2) NOT NULL DEFAULT 25000.00,
  `joining_date` DATE NOT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `photo` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `employees` (`employee_id`, `full_name`, `email`, `phone`, `role`, `designation`, `qualification`, `skills`, `base_salary`, `joining_date`, `status`) VALUES
('PC-EMP-001', 'Sanjay Saini', 'sanjay@propercomputer.com', '9822114455', 'Teacher', 'Senior IT Instructor', 'MCA, B.Sc Computer Science', 'Photoshop, MS Excel, MS Word, Web Dev', 35000.00, '2023-01-15', 'Active'),
('PC-EMP-002', 'Ravina Saini', 'ravina@propercomputer.com', '9822114456', 'Teacher', 'Full Stack Trainer', 'B.Tech IT, M.Tech', 'C#, .NET, C, C++, Python, DSA', 32000.00, '2023-04-10', 'Active'),
('PC-EMP-003', 'Nazmeen Bano', 'nazmeen@propercomputer.com', '9822114457', 'Teacher', 'Accounts & Commerce Trainer', 'M.Com, Tally Certified', 'Tally Prime, GST, MS Word', 28000.00, '2023-08-01', 'Active'),
('PC-EMP-004', 'Naushina Zamzam', 'naushina@propercomputer.com', '9822114458', 'Teacher', 'Office Automation Trainer', 'B.Com, Advanced Diploma in IT', 'Tally Prime, GST, MS Excel, Office 365', 28000.00, '2024-01-10', 'Active');

-- -----------------------------------------------------------------------------
-- 7. Students Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admission_no` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NULL,
  `phone` VARCHAR(20) NOT NULL UNIQUE,
  `alt_phone` VARCHAR(20) NULL,
  `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
  `dob` DATE NULL,
  `address` TEXT NULL,
  `education` VARCHAR(100) NULL,
  `status` ENUM('Active', 'Completed', 'Dropped') DEFAULT 'Active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `students` (`admission_no`, `full_name`, `email`, `phone`, `gender`, `dob`, `address`, `education`, `status`) VALUES
('PC-ADM-2026-0001', 'Shafeen Sheikh', 'shafeen@gmail.com', '7020031922', 'Female', '2004-05-12', 'Kamptee, Nagpur', '12th Pass', 'Active'),
('PC-ADM-2026-0002', 'Shagun Murai', 'shagun@gmail.com', '8379066454', 'Female', '2003-08-19', 'Civil Lines, Nagpur', 'B.Com 2nd Year', 'Active'),
('PC-ADM-2026-0003', 'Vidhanshu Meshram', 'vidhanshu@gmail.com', '9021731900', 'Male', '2002-11-23', 'Wardha Road, Nagpur', 'B.Sc Computer Science', 'Active'),
('PC-ADM-2026-0004', 'Esha Kottawar', 'esha@gmail.com', '9322096897', 'Female', '2004-01-14', 'Kamptee Main Road', 'B.Com 1st Year', 'Active'),
('PC-ADM-2026-0005', 'Izma Taqqees', 'izma@gmail.com', '9763245105', 'Female', '2003-03-30', 'Sitabuldi, Nagpur', 'B.A.', 'Active'),
('PC-ADM-2026-0006', 'Zafreen Fatima', 'zafreen@gmail.com', '9373079028', 'Female', '2002-09-08', 'Kamptee', 'B.Sc IT', 'Active'),
('PC-ADM-2026-0007', 'Rohit Patre', 'rohit@gmail.com', '9822604510', 'Male', '2001-07-22', 'Sadar, Nagpur', 'Diploma in Engineering', 'Active'),
('PC-ADM-2026-0008', 'Rashi Biturwar', 'rashi@gmail.com', '8806105461', 'Female', '2003-12-05', 'Nandanvan, Nagpur', 'B.Com', 'Active');

-- -----------------------------------------------------------------------------
-- 8. Enquiries Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `enquiries`;
CREATE TABLE `enquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NULL,
  `course_id` INT NOT NULL,
  `mode` ENUM('Offline', 'Online', 'Hybrid') DEFAULT 'Hybrid',
  `lead_source` VARCHAR(50) DEFAULT 'Walkin',
  `lead_status` ENUM('Walkin', 'Telephonic', 'Interested', 'Followup', 'Admission Done', 'Cancelled') DEFAULT 'Walkin',
  `enquiry_date` DATE NOT NULL,
  `follow_up_date` DATE NULL,
  `assigned_to` INT NULL,
  `notes` TEXT NULL,
  `is_converted` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  FOREIGN KEY (`assigned_to`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `enquiries` (`student_name`, `phone`, `email`, `course_id`, `mode`, `lead_source`, `lead_status`, `enquiry_date`, `follow_up_date`, `assigned_to`, `notes`, `is_converted`) VALUES
('Shafeen Sheikh', '7020031922', 'shafeen@gmail.com', 1, 'Hybrid', 'Walkin', 'Admission Done', '2026-04-28', '2026-05-02', 1, 'Enquired for PHP/MySQL. Converted to admission.', 1),
('Shagun Murai', '8379066454', 'shagun@gmail.com', 2, 'Hybrid', 'Walkin', 'Admission Done', '2026-03-28', '2026-04-01', 2, 'Visited center with parents. Joined MERN stack.', 1),
('Vidhanshu Meshram', '9021731900', 'vidhanshu@gmail.com', 20, 'Hybrid', 'Telephonic', 'Interested', '2026-03-28', '2026-10-30', 2, 'Inquired about Data Science weekend batch.', 0),
('Humanshi Wanjari', '9923388817', 'humanshi@gmail.com', 7, 'Offline', 'Walkin', 'Followup', '2026-03-23', '2026-10-29', 1, 'Considering C++ & DSA before college semester.', 0),
('Abhishek Mahaidar', '9960831187', 'abhishek@gmail.com', 8, 'Hybrid', 'Walkin', 'Interested', '2026-03-23', '2026-10-31', 2, 'Looking for Python certification course.', 0),
('Rupam Raibole', '9763468269', 'rupam@gmail.com', 34, 'Offline', 'Telephonic', 'Walkin', '2026-02-14', '2026-11-05', 1, 'Interested in Ethical Hacking & Linux essentials.', 0);

-- -----------------------------------------------------------------------------
-- 9. Enquiry Follow-ups Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `enquiry_followups`;
CREATE TABLE `enquiry_followups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `enquiry_id` INT NOT NULL,
  `followup_date` DATE NOT NULL,
  `call_summary` TEXT NOT NULL,
  `next_action_date` DATE NULL,
  `status` VARCHAR(50) DEFAULT 'Pending',
  `created_by` INT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `enquiry_followups` (`enquiry_id`, `followup_date`, `call_summary`, `next_action_date`, `status`, `created_by`) VALUES
(3, '2026-03-30', 'Explained course syllabus and placement assistance. Student will confirm after college exams.', '2026-10-30', 'Followup', 1),
(4, '2026-03-25', 'Offered 10% student discount on weekday morning batch. Requested callback next week.', '2026-10-29', 'Pending', 1);

-- -----------------------------------------------------------------------------
-- 10. Admissions Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `admissions`;
CREATE TABLE `admissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admission_no` VARCHAR(50) NOT NULL UNIQUE,
  `student_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  `batch_time` VARCHAR(50) NOT NULL DEFAULT '10:00 am-11:00 am',
  `track` ENUM('Daily', 'Alternate', 'Weekend') DEFAULT 'Daily',
  `total_fees` DECIMAL(10,2) NOT NULL,
  `discount` DECIMAL(10,2) DEFAULT 0.00,
  `net_fees` DECIMAL(10,2) NOT NULL,
  `paid_fees` DECIMAL(10,2) DEFAULT 0.00,
  `due_fees` DECIMAL(10,2) NOT NULL,
  `admission_date` DATE NOT NULL,
  `status` ENUM('Ongoing', 'Complete', 'Cancel') DEFAULT 'Ongoing',
  `assigned_teacher` INT NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  FOREIGN KEY (`assigned_teacher`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admissions` (`admission_no`, `student_id`, `course_id`, `batch_time`, `track`, `total_fees`, `discount`, `net_fees`, `paid_fees`, `due_fees`, `admission_date`, `status`, `assigned_teacher`) VALUES
('PC-ADM-2026-0001', 1, 1, '10:00 am-11:00 am', 'Daily', 15000.00, 1000.00, 14000.00, 14000.00, 0.00, '2026-04-28', 'Ongoing', 1),
('PC-ADM-2026-0002', 2, 2, '11:00 am-12:00 pm', 'Daily', 22000.00, 2000.00, 20000.00, 15000.00, 5000.00, '2026-03-28', 'Ongoing', 2),
('PC-ADM-2026-0003', 3, 20, '09:00 am-10:00 am', 'Daily', 35000.00, 0.00, 35000.00, 25000.00, 10000.00, '2026-03-28', 'Ongoing', 2),
('PC-ADM-2026-0004', 4, 21, '10:00 am-11:00 am', 'Daily', 20000.00, 1000.00, 19000.00, 19000.00, 0.00, '2026-02-15', 'Complete', 1),
('PC-ADM-2026-0005', 5, 24, '12:00 pm-01:00 pm', 'Daily', 12000.00, 0.00, 12000.00, 12000.00, 0.00, '2026-01-10', 'Complete', 4),
('PC-ADM-2026-0006', 6, 8, '04:00 pm-05:00 pm', 'Daily', 14000.00, 0.00, 14000.00, 8000.00, 6000.00, '2026-05-10', 'Ongoing', 2),
('PC-ADM-2026-0007', 7, 7, '06:00 pm-07:00 pm', 'Daily', 12000.00, 1000.00, 11000.00, 5000.00, 6000.00, '2026-06-05', 'Ongoing', 2),
('PC-ADM-2026-0008', 8, 43, '05:00 pm-06:00 pm', 'Daily', 15000.00, 0.00, 15000.00, 15000.00, 0.00, '2026-03-12', 'Ongoing', 1);

-- -----------------------------------------------------------------------------
-- 11. Fee Payments Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `fee_payments`;
CREATE TABLE `fee_payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admission_id` INT NOT NULL,
  `receipt_no` VARCHAR(50) NOT NULL UNIQUE,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_mode` ENUM('Cash', 'Bank Transfer', 'UPI', 'Cheque', 'Card') DEFAULT 'Cash',
  `reference_no` VARCHAR(100) NULL,
  `payment_date` DATE NOT NULL,
  `remarks` VARCHAR(255) NULL,
  `collected_by` INT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `fee_payments` (`admission_id`, `receipt_no`, `amount`, `payment_mode`, `reference_no`, `payment_date`, `remarks`, `collected_by`) VALUES
(1, 'PC-REC-2604-0001', 7000.00, 'UPI', 'UPI/2604/78912', '2026-04-28', '1st Installment', 1),
(1, 'PC-REC-2605-0002', 7000.00, 'Cash', 'CASH-0021', '2026-05-25', 'Full Payment Clear', 1),
(2, 'PC-REC-2603-0003', 10000.00, 'Bank Transfer', 'NEFT-543189', '2026-03-28', 'Admission Down Payment', 1),
(2, 'PC-REC-2605-0004', 5000.00, 'UPI', 'UPI/2605/11290', '2026-05-15', '2nd Installment', 1),
(3, 'PC-REC-2603-0005', 25000.00, 'Bank Transfer', 'IMPS-908123', '2026-03-28', 'Part Payment for Data Science', 1),
(4, 'PC-REC-2602-0006', 19000.00, 'UPI', 'UPI/2602/99001', '2026-02-15', 'Full payment with course discount', 1),
(5, 'PC-REC-2601-0007', 12000.00, 'Cash', 'CASH-0012', '2026-01-10', 'Full course fee upfront', 1),
(6, 'PC-REC-2605-0008', 8000.00, 'UPI', 'UPI/2605/44321', '2026-05-10', 'Registration and initial installment', 1),
(7, 'PC-REC-2606-0009', 5000.00, 'Cash', 'CASH-0033', '2026-06-05', 'First installment for C++', 1),
(8, 'PC-REC-2603-0010', 15000.00, 'UPI', 'UPI/2603/66712', '2026-03-12', 'Full payment for Graphic Design', 1);

-- -----------------------------------------------------------------------------
-- 12. Receipts Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `receipts`;
CREATE TABLE `receipts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receipt_no` VARCHAR(50) NOT NULL UNIQUE,
  `payment_id` INT NOT NULL,
  `admission_id` INT NOT NULL,
  `receipt_date` DATE NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_mode` VARCHAR(50) NOT NULL,
  `total_course_fee` DECIMAL(10,2) NOT NULL,
  `fee_paid_so_far` DECIMAL(10,2) NOT NULL,
  `remaining_due` DECIMAL(10,2) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`payment_id`) REFERENCES `fee_payments` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `receipts` (`receipt_no`, `payment_id`, `admission_id`, `receipt_date`, `amount`, `payment_mode`, `total_course_fee`, `fee_paid_so_far`, `remaining_due`) VALUES
('PC-REC-2604-0001', 1, 1, '2026-04-28', 7000.00, 'UPI', 14000.00, 7000.00, 7000.00),
('PC-REC-2605-0002', 2, 1, '2026-05-25', 7000.00, 'Cash', 14000.00, 14000.00, 0.00),
('PC-REC-2603-0003', 3, 2, '2026-03-28', 10000.00, 'Bank Transfer', 20000.00, 10000.00, 10000.00),
('PC-REC-2605-0004', 4, 2, '2026-05-15', 5000.00, 'UPI', 20000.00, 15000.00, 5000.00),
('PC-REC-2603-0005', 5, 3, '2026-03-28', 25000.00, 'Bank Transfer', 35000.00, 25000.00, 10000.00),
('PC-REC-2602-0006', 6, 4, '2026-02-15', 19000.00, 'UPI', 19000.00, 19000.00, 0.00),
('PC-REC-2601-0007', 7, 5, '2026-01-10', 12000.00, 'Cash', 12000.00, 12000.00, 0.00),
('PC-REC-2605-0008', 8, 6, '2026-05-10', 8000.00, 'UPI', 14000.00, 8000.00, 6000.00),
('PC-REC-2606-0009', 9, 7, '2026-06-05', 5000.00, 'Cash', 11000.00, 5000.00, 6000.00),
('PC-REC-2603-0010', 10, 8, '2026-03-12', 15000.00, 'UPI', 15000.00, 15000.00, 0.00);

-- -----------------------------------------------------------------------------
-- 13. Attendance Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `attendance_date` DATE NOT NULL,
  `status` ENUM('Present', 'Absent', 'Half Day', 'Leave') DEFAULT 'Present',
  `in_time` TIME NULL,
  `out_time` TIME NULL,
  `remarks` VARCHAR(255) NULL,
  `is_approved` TINYINT(1) DEFAULT 1,
  `approved_by` INT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `attendance` (`employee_id`, `attendance_date`, `status`, `in_time`, `out_time`, `is_approved`) VALUES
(1, CURRENT_DATE(), 'Present', '09:30:00', '18:00:00', 1),
(2, CURRENT_DATE(), 'Present', '09:45:00', '18:15:00', 1),
(3, CURRENT_DATE(), 'Present', '09:30:00', '18:00:00', 1),
(4, CURRENT_DATE(), 'Present', '10:00:00', '18:30:00', 1);

-- -----------------------------------------------------------------------------
-- 14. Leave Applications Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `leave_applications`;
CREATE TABLE `leave_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `leave_type` ENUM('Casual Leave', 'Sick Leave', 'Earned Leave', 'Maternity/Paternity') DEFAULT 'Casual Leave',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_days` INT NOT NULL DEFAULT 1,
  `reason` TEXT NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `approved_by` INT NULL,
  `response_notes` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `leave_applications` (`employee_id`, `leave_type`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `approved_by`, `response_notes`) VALUES
(2, 'Casual Leave', '2026-11-02', '2026-11-03', 2, 'Attending family wedding ceremony', 'Approved', 1, 'Approved. Batch lectures adjusted with Sanjay Sir.'),
(3, 'Sick Leave', '2026-11-10', '2026-11-11', 2, 'Medical checkup and recovery', 'Pending', NULL, NULL);

-- -----------------------------------------------------------------------------
-- 15. Salary Records Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `salary_records`;
CREATE TABLE `salary_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `month` INT NOT NULL,
  `year` INT NOT NULL,
  `working_days` INT NOT NULL DEFAULT 26,
  `attended_days` INT NOT NULL DEFAULT 26,
  `leave_days` INT NOT NULL DEFAULT 0,
  `basic_salary` DECIMAL(10,2) NOT NULL,
  `allowances` DECIMAL(10,2) DEFAULT 0.00,
  `deductions` DECIMAL(10,2) DEFAULT 0.00,
  `net_salary` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Paid', 'Pending') DEFAULT 'Paid',
  `payment_date` DATE NULL,
  `payment_mode` VARCHAR(50) DEFAULT 'Bank Transfer',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `salary_records` (`employee_id`, `month`, `year`, `working_days`, `attended_days`, `leave_days`, `basic_salary`, `allowances`, `deductions`, `net_salary`, `status`, `payment_date`, `payment_mode`) VALUES
(1, 9, 2026, 26, 26, 0, 35000.00, 2000.00, 1000.00, 36000.00, 'Paid', '2026-10-02', 'Bank Transfer'),
(2, 9, 2026, 26, 25, 1, 32000.00, 1500.00, 1200.00, 32300.00, 'Paid', '2026-10-02', 'Bank Transfer'),
(3, 9, 2026, 26, 26, 0, 28000.00, 1000.00, 500.00, 28500.00, 'Paid', '2026-10-02', 'Bank Transfer'),
(4, 9, 2026, 26, 26, 0, 28000.00, 1000.00, 500.00, 28500.00, 'Paid', '2026-10-02', 'Bank Transfer');

-- -----------------------------------------------------------------------------
-- 16. Monthly Targets Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `monthly_targets`;
CREATE TABLE `monthly_targets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `month` VARCHAR(20) NOT NULL,
  `year` INT NOT NULL,
  `target_admissions` INT NOT NULL DEFAULT 30,
  `target_billing` DECIMAL(12,2) NOT NULL DEFAULT 150000.00,
  `target_collection` DECIMAL(12,2) NOT NULL DEFAULT 120000.00,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `monthly_targets` (`month`, `year`, `target_admissions`, `target_billing`, `target_collection`) VALUES
('September', 2025, 50, 200000.00, 150000.00),
('October', 2025, 30, 150000.00, 100000.00),
('November', 2025, 30, 150000.00, 100000.00),
('May', 2026, 50, 200000.00, 150000.00),
('June', 2026, 35, 175000.00, 150000.00),
('July', 2026, 35, 200000.00, 150000.00),
('October', 2026, 40, 220000.00, 180000.00);

-- -----------------------------------------------------------------------------
-- 17. Monthly Budgets & Expenses Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `monthly_budgets`;
CREATE TABLE `monthly_budgets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `month` VARCHAR(20) NOT NULL,
  `year` INT NOT NULL,
  `budgeted_amount` DECIMAL(12,2) NOT NULL,
  `notes` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `monthly_budgets` (`month`, `year`, `budgeted_amount`, `notes`) VALUES
('September', 2026, 120000.00, 'Operating overheads, teacher salaries, marketing'),
('October', 2026, 150000.00, 'Diwali marketing campaign, hardware upgrades, lab maintenance');

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `expense_date` DATE NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_mode` VARCHAR(50) DEFAULT 'Cash',
  `receipt_voucher` VARCHAR(50) NULL,
  `paid_to` VARCHAR(100) NOT NULL,
  `remarks` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `expenses` (`expense_date`, `category`, `title`, `amount`, `payment_mode`, `receipt_voucher`, `paid_to`, `remarks`) VALUES
('2026-10-05', 'Rent & Electricity', 'Institute Monthly Rent', 35000.00, 'Bank Transfer', 'VCH-1001', 'Commercial Complex Realty', 'October premises lease rent'),
('2026-10-10', 'Marketing', 'Local Newspaper & Banner Ads', 12500.00, 'UPI', 'VCH-1002', 'City Media Advertising', 'Admission drive promotional campaigns'),
('2026-10-15', 'Lab Equipment', 'SSD & RAM Upgrades for Coding Lab', 18400.00, 'Card', 'VCH-1003', 'Apex Computers', 'Upgraded 8 systems for MERN and AI tools');

-- -----------------------------------------------------------------------------
-- 18. Student Notices Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NOT NULL,
  `target_audience` ENUM('All', 'Students', 'Faculty', 'Batches') DEFAULT 'All',
  `priority` ENUM('Normal', 'High', 'Urgent') DEFAULT 'Normal',
  `expiry_date` DATE NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notices` (`title`, `content`, `target_audience`, `priority`, `expiry_date`, `is_active`) VALUES
('Diwali Festival Holiday Schedule', 'Proper Computer Institute will remain closed from 31st October to 4th November for Diwali festivities. Regular lab sessions resume on 5th November.', 'All', 'High', '2026-11-06', 1),
('New Full Stack & Python Batches Commencing', 'Fresh weekend and weekday morning batches for MERN Stack, Python DSA, and Data Analytics commence from 10th November. Register at front desk.', 'Students', 'Normal', '2026-11-15', 1),
('Practical Assessment & Project Submissions', 'All continuing students in Web Development with PHP/MySQL and Graphic Design must submit their capstone projects by Friday.', 'Students', 'Urgent', '2026-11-08', 1);

-- -----------------------------------------------------------------------------
-- 19. Activity Logs Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `record_id` INT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `activity_logs` (`user_id`, `action`, `module`, `record_id`, `details`, `ip_address`) VALUES
(1, 'System Installed', 'Core', NULL, 'Proper Computer database tables and seed data created.', '127.0.0.1'),
(1, 'Payment Recorded', 'Fees', 1, 'Collected 7000 via UPI for admission PC-ADM-2026-0001', '127.0.0.1');

-- -----------------------------------------------------------------------------
-- 20. Website Settings Table
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `website_settings`;
CREATE TABLE `website_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(50) DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `website_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('institute_name', 'Proper Computer', 'general'),
('institute_tagline', 'Premier IT, Coding & Management Institute', 'general'),
('institute_address', 'Proper Computer Academy, 2nd Floor, Main IT Hub, Kamptee / Nagpur - 441001', 'contact'),
('institute_phone', '+91 98223 34455 / +91 70200 31922', 'contact'),
('institute_email', 'info@propercomputer.com', 'contact'),
('institute_website', 'www.propercomputer.com', 'contact'),
('currency_symbol', '₹', 'finance'),
('admission_prefix', 'PC-ADM-', 'system'),
('receipt_prefix', 'PC-REC-', 'system'),
('facebook_url', 'https://facebook.com/propercomputer', 'social'),
('instagram_url', 'https://instagram.com/propercomputer', 'social'),
('linkedin_url', 'https://linkedin.com/company/propercomputer', 'social'),
('youtube_url', 'https://youtube.com/@propercomputer', 'social');

SET FOREIGN_KEY_CHECKS = 1;
