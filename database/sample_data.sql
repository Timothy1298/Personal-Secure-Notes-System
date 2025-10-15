-- Sample Data Population for Secure Notes System
-- This script populates the database with comprehensive sample data
-- Using existing user: timothy (ID: 1)

-- Insert sample tags (10 tags)
INSERT INTO `tags` (`user_id`, `name`, `color`, `icon`, `description`, `usage_count`) VALUES
(1, 'Work', '#3b82f6', 'fas fa-briefcase', 'Work-related tasks and notes', 15),
(1, 'Personal', '#10b981', 'fas fa-user', 'Personal tasks and notes', 12),
(1, 'Health', '#ef4444', 'fas fa-heart', 'Health and fitness related', 8),
(1, 'Finance', '#f59e0b', 'fas fa-dollar-sign', 'Financial planning and budgeting', 6),
(1, 'Learning', '#8b5cf6', 'fas fa-graduation-cap', 'Educational content and courses', 10),
(1, 'Travel', '#06b6d4', 'fas fa-plane', 'Travel plans and itineraries', 4),
(1, 'Shopping', '#ec4899', 'fas fa-shopping-cart', 'Shopping lists and purchases', 7),
(1, 'Home', '#84cc16', 'fas fa-home', 'Home improvement and maintenance', 9),
(1, 'Family', '#f97316', 'fas fa-users', 'Family events and activities', 11),
(1, 'Projects', '#6366f1', 'fas fa-project-diagram', 'Project management and planning', 13);

-- Insert sample notes (10 notes with various features)
INSERT INTO `notes` (`user_id`, `title`, `content`, `summary`, `color`, `priority`, `is_pinned`, `is_archived`, `word_count`, `read_time`) VALUES
(1, 'Project Planning Meeting Notes', 'Attended the quarterly project planning meeting today. Key points discussed:\n\n1. Q4 Objectives:\n   - Launch new mobile app by December\n   - Increase user engagement by 25%\n   - Implement new security features\n\n2. Resource Allocation:\n   - Frontend team: 3 developers\n   - Backend team: 2 developers\n   - QA team: 1 tester\n\n3. Timeline:\n   - Week 1-2: Design and prototyping\n   - Week 3-6: Development phase\n   - Week 7-8: Testing and bug fixes\n   - Week 9-10: Deployment and launch\n\nNext meeting scheduled for next Friday at 2 PM.', 'Quarterly project planning meeting covering Q4 objectives, resource allocation, and timeline for mobile app launch.', '#e0f2fe', 'high', 1, 0, 156, 1),
(1, 'Healthy Recipe Collection', '## Mediterranean Quinoa Bowl\n\n**Ingredients:**\n- 1 cup quinoa\n- 1 cucumber, diced\n- 1 cup cherry tomatoes\n- 1/2 red onion, sliced\n- 1/2 cup kalamata olives\n- 1/4 cup feta cheese\n- 2 tbsp olive oil\n- 1 tbsp lemon juice\n- Salt and pepper to taste\n\n**Instructions:**\n1. Cook quinoa according to package directions\n2. Let cool completely\n3. Mix all vegetables in a large bowl\n4. Add cooled quinoa\n5. Drizzle with olive oil and lemon juice\n6. Season with salt and pepper\n7. Top with feta cheese\n\n**Nutritional Info:**\n- Calories: 320 per serving\n- Protein: 12g\n- Fiber: 6g', 'Collection of healthy Mediterranean recipes with detailed ingredients and nutritional information.', '#f0fdf4', 'medium', 0, 0, 198, 2),
(1, 'Investment Portfolio Review', '## Portfolio Performance - October 2025\n\n**Current Holdings:**\n- Tech Stocks (40%): AAPL, GOOGL, MSFT, TSLA\n- Index Funds (30%): VTI, VXUS\n- Bonds (20%): BND, TLT\n- Crypto (10%): BTC, ETH\n\n**Performance Summary:**\n- YTD Return: +12.5%\n- Monthly Return: +2.3%\n- Best Performer: TSLA (+8.2%)\n- Worst Performer: BND (-1.1%)\n\n**Action Items:**\n1. Rebalance portfolio to maintain 40/30/20/10 allocation\n2. Consider adding REITs for diversification\n3. Review bond allocation given current interest rates\n4. Set up automatic monthly contributions\n\n**Next Review:** November 15, 2025', 'Monthly investment portfolio review with performance analysis and rebalancing recommendations.', '#fef3c7', 'high', 1, 0, 167, 2),
(1, 'Travel Itinerary - Japan 2025', '## 10-Day Japan Adventure\n\n**Day 1-3: Tokyo**\n- Arrival at Narita Airport\n- Hotel: Park Hyatt Tokyo\n- Activities: Senso-ji Temple, Tsukiji Fish Market, Shibuya Crossing\n- Restaurants: Sushi Dai, Ramen Nagi\n\n**Day 4-6: Kyoto**\n- Bullet train to Kyoto\n- Hotel: The Ritz-Carlton Kyoto\n- Activities: Fushimi Inari Shrine, Arashiyama Bamboo Grove, Kinkaku-ji\n- Restaurants: Kikunoi, Giro Giro Hitoshina\n\n**Day 7-9: Osaka**\n- Train to Osaka\n- Hotel: Conrad Osaka\n- Activities: Osaka Castle, Dotonbori, Universal Studios\n- Restaurants: Kani Doraku, Okonomiyaki Chitose\n\n**Day 10: Return**\n- Flight from Kansai Airport\n- Souvenirs: Matcha tea, traditional crafts\n\n**Budget:** $3,500 per person\n**Travel Insurance:** Covered\n**Visa:** Not required for US citizens', 'Detailed 10-day Japan travel itinerary covering Tokyo, Kyoto, and Osaka with accommodations, activities, and dining recommendations.', '#fce7f3', 'medium', 0, 0, 189, 3),
(1, 'Learning Path - Machine Learning', '## ML Learning Journey 2025\n\n**Phase 1: Foundations (Weeks 1-4)**\n- Mathematics: Linear Algebra, Calculus, Statistics\n- Programming: Python, NumPy, Pandas\n- Resources: Khan Academy, Coursera ML Course\n\n**Phase 2: Core Concepts (Weeks 5-8)**\n- Supervised Learning: Regression, Classification\n- Unsupervised Learning: Clustering, Dimensionality Reduction\n- Model Evaluation: Cross-validation, Metrics\n- Resources: Scikit-learn documentation, Andrew Ng course\n\n**Phase 3: Deep Learning (Weeks 9-12)**\n- Neural Networks: Perceptrons, Backpropagation\n- Frameworks: TensorFlow, PyTorch\n- Applications: Computer Vision, NLP\n- Resources: Deep Learning Specialization, Fast.ai\n\n**Phase 4: Projects (Weeks 13-16)**\n- Project 1: Image Classification\n- Project 2: Sentiment Analysis\n- Project 3: Recommendation System\n- Portfolio: GitHub repository\n\n**Goals:**\n- Complete 4 projects by end of year\n- Contribute to open source ML projects\n- Attend 2 ML conferences', 'Comprehensive 16-week machine learning learning path with phases, resources, and project goals.', '#ede9fe', 'high', 1, 0, 201, 3),
(1, 'Home Renovation Checklist', '## Kitchen Renovation Project\n\n**Phase 1: Planning & Design**\n- [x] Measure kitchen dimensions\n- [x] Create 3D design mockup\n- [x] Get contractor quotes (3 received)\n- [x] Choose materials and finishes\n- [ ] Finalize budget ($25,000)\n- [ ] Obtain permits\n\n**Phase 2: Demolition**\n- [ ] Remove old cabinets\n- [ ] Remove old countertops\n- [ ] Remove old appliances\n- [ ] Dispose of debris\n- [ ] Clean and prep space\n\n**Phase 3: Installation**\n- [ ] Install new electrical outlets\n- [ ] Install new plumbing\n- [ ] Install new cabinets\n- [ ] Install new countertops\n- [ ] Install new appliances\n- [ ] Install new flooring\n\n**Phase 4: Finishing**\n- [ ] Paint walls\n- [ ] Install backsplash\n- [ ] Install lighting fixtures\n- [ ] Final cleaning\n- [ ] Move back in\n\n**Timeline:** 6-8 weeks\n**Contractor:** ABC Construction\n**Materials:** Home Depot, Lowes', 'Detailed kitchen renovation checklist with phases, tasks, timeline, and contractor information.', '#f0f9ff', 'medium', 0, 0, 178, 2),
(1, 'Family Reunion Planning', '## Annual Family Reunion 2025\n\n**Event Details:**\n- Date: July 15, 2025\n- Time: 10:00 AM - 6:00 PM\n- Location: Central Park, Pavilion #3\n- Expected Guests: 45-50 people\n\n**Planning Committee:**\n- Coordinator: Mom (Sarah)\n- Food: Aunt Mary, Cousin Lisa\n- Activities: Uncle John, Sister Emma\n- Decorations: Grandma Rose\n\n**Menu Planning:**\n- Main Course: BBQ (burgers, hot dogs, chicken)\n- Sides: Potato salad, coleslaw, baked beans\n- Desserts: Apple pie, chocolate cake, ice cream\n- Beverages: Lemonade, iced tea, water, soda\n- Special: Vegetarian options for 5 guests\n\n**Activities:**\n- 10:00 AM: Welcome and introductions\n- 11:00 AM: Family photo session\n- 12:00 PM: Lunch and socializing\n- 2:00 PM: Games and activities\n- 4:00 PM: Family talent show\n- 5:00 PM: Cake and closing\n\n**Budget:** $800\n**RSVP Deadline:** June 30, 2025', 'Comprehensive family reunion planning with event details, committee assignments, menu, activities, and budget.', '#fef2f2', 'medium', 0, 0, 192, 3),
(1, 'Shopping List - Holiday Season', '## Holiday Shopping 2025\n\n**Gift List:**\n- Mom: Spa gift certificate ($100)\n- Dad: New golf clubs ($300)\n- Sister: Designer handbag ($250)\n- Brother: Gaming console ($400)\n- Grandma: Photo album with family pics ($50)\n- Grandpa: Fishing gear ($150)\n- Niece: Art supplies set ($75)\n- Nephew: LEGO set ($80)\n- Best Friend: Wine tasting experience ($120)\n- Colleague: Gift card ($25)\n\n**Home Decorations:**\n- Christmas tree (7ft artificial)\n- String lights (LED, warm white)\n- Ornaments (glass and plastic mix)\n- Wreath for front door\n- Stockings (6 pieces)\n- Tree skirt\n- Candles and candle holders\n\n**Food & Beverages:**\n- Turkey (12-14 lbs)\n- Ham (8 lbs)\n- Stuffing mix\n- Cranberry sauce\n- Green beans\n- Sweet potatoes\n- Dinner rolls\n- Wine (red and white)\n- Eggnog\n- Hot chocolate mix\n\n**Total Budget:** $2,000\n**Shopping Strategy:**\n- Week 1: Online orders (gifts)\n- Week 2: Local stores (decorations)\n- Week 3: Grocery stores (food)\n- Week 4: Last-minute items', 'Comprehensive holiday shopping list with gifts, decorations, food, and strategic shopping plan.', '#f0fdf4', 'low', 0, 0, 185, 2),
(1, 'Health & Fitness Goals 2025', '## Annual Health & Fitness Plan\n\n**Current Stats:**\n- Weight: 180 lbs\n- Body Fat: 18%\n- BMI: 24.2\n- Resting Heart Rate: 65 bpm\n\n**Goals for 2025:**\n- Weight: 170 lbs (lose 10 lbs)\n- Body Fat: 15%\n- Run 5K in under 25 minutes\n- Complete 100 push-ups in one set\n- Deadlift 225 lbs\n- Meditate daily for 10 minutes\n\n**Workout Schedule:**\n- Monday: Upper body strength training\n- Tuesday: Cardio (running/cycling)\n- Wednesday: Lower body strength training\n- Thursday: Yoga/Pilates\n- Friday: Full body HIIT\n- Saturday: Outdoor activities (hiking/biking)\n- Sunday: Rest and recovery\n\n**Nutrition Plan:**\n- Calorie target: 2,200 per day\n- Protein: 150g daily\n- Carbs: 200g daily\n- Fats: 80g daily\n- Water: 3 liters daily\n- Supplements: Multivitamin, Omega-3, Protein powder\n\n**Progress Tracking:**\n- Weekly weigh-ins\n- Monthly body measurements\n- Fitness app logging\n- Photo progress (monthly)\n\n**Milestones:**\n- Q1: Lose 3 lbs, establish routine\n- Q2: Lose 3 lbs, improve running time\n- Q3: Lose 2 lbs, increase strength\n- Q4: Lose 2 lbs, achieve all goals', 'Comprehensive health and fitness plan with current stats, goals, workout schedule, nutrition plan, and progress tracking.', '#fef2f2', 'high', 1, 0, 203, 3),
(1, 'Book Reading List 2025', '## 2025 Reading Challenge\n\n**Goal:** Read 24 books (2 per month)\n\n**Completed (6 books):**\n1. "Atomic Habits" by James Clear ⭐⭐⭐⭐⭐\n2. "The Lean Startup" by Eric Ries ⭐⭐⭐⭐\n3. "Sapiens" by Yuval Noah Harari ⭐⭐⭐⭐⭐\n4. "Thinking, Fast and Slow" by Daniel Kahneman ⭐⭐⭐⭐\n5. "The Psychology of Money" by Morgan Housel ⭐⭐⭐⭐⭐\n6. "Deep Work" by Cal Newport ⭐⭐⭐⭐\n\n**Currently Reading:**\n7. "The 7 Habits of Highly Effective People" by Stephen Covey\n8. "Educated" by Tara Westover\n\n**To Read (16 books):**\n9. "The Power of Now" by Eckhart Tolle\n10. "Man\'s Search for Meaning" by Viktor Frankl\n11. "The Alchemist" by Paulo Coelho\n12. "1984" by George Orwell\n13. "To Kill a Mockingbird" by Harper Lee\n14. "The Great Gatsby" by F. Scott Fitzgerald\n15. "Pride and Prejudice" by Jane Austen\n16. "The Catcher in the Rye" by J.D. Salinger\n17. "The Lord of the Rings" by J.R.R. Tolkien\n18. "Dune" by Frank Herbert\n19. "The Handmaid\'s Tale" by Margaret Atwood\n20. "The Kite Runner" by Khaled Hosseini\n21. "The Book Thief" by Markus Zusak\n22. "The Help" by Kathryn Stockett\n23. "The Fault in Our Stars" by John Green\n24. "The Martian" by Andy Weir\n\n**Reading Schedule:**\n- Weekdays: 30 minutes before bed\n- Weekends: 1 hour in the morning\n- Commute: Audiobooks (2 hours daily)\n\n**Genres:**\n- Self-help: 6 books\n- Fiction: 8 books\n- Business: 4 books\n- History: 3 books\n- Science: 3 books', 'Comprehensive 2025 reading challenge with 24 books, progress tracking, and reading schedule across multiple genres.', '#fef3c7', 'medium', 0, 0, 196, 3);

-- Insert sample tasks (10 tasks with various features)
INSERT INTO `tasks` (`user_id`, `title`, `description`, `status`, `priority`, `progress`, `due_date`, `is_recurring`, `recurring_pattern`, `estimated_time`, `actual_time`) VALUES
(1, 'Complete Q4 Project Proposal', 'Prepare comprehensive project proposal for Q4 mobile app development including timeline, budget, resource allocation, and risk assessment. Include market research, competitor analysis, and technical specifications.', 'in_progress', 'urgent', 65, '2025-10-20 17:00:00', 0, NULL, 480, 312),
(1, 'Schedule Annual Health Checkup', 'Book annual physical examination with Dr. Smith. Include blood work, cholesterol check, blood pressure monitoring, and discuss any health concerns. Bring list of current medications and supplements.', 'pending', 'medium', 0, '2025-11-15 09:00:00', 1, 'yearly', 120, 0),
(1, 'Renew Car Insurance Policy', 'Compare insurance quotes from 3 different providers. Current policy expires on December 15th. Consider adding roadside assistance and rental car coverage. Review coverage limits and deductibles.', 'pending', 'high', 0, '2025-12-10 23:59:59', 1, 'yearly', 180, 0),
(1, 'Plan Thanksgiving Dinner Menu', 'Create detailed menu for 12 guests including appetizers, main course, sides, desserts, and beverages. Consider dietary restrictions (2 vegetarians, 1 gluten-free). Create shopping list and cooking timeline.', 'pending', 'medium', 0, '2025-11-20 18:00:00', 1, 'yearly', 240, 0),
(1, 'Update LinkedIn Profile', 'Refresh professional profile with recent achievements, skills, and experience. Add new project portfolio, update headline, and request recommendations from colleagues. Optimize for relevant keywords.', 'completed', 'low', 100, '2025-10-10 16:00:00', 0, NULL, 90, 87),
(1, 'Learn Spanish - Complete Level A2', 'Finish Duolingo Spanish course Level A2. Practice speaking with native speakers twice a week. Complete 5 lessons per day. Focus on past tense and future tense conjugations.', 'in_progress', 'medium', 40, '2025-12-31 23:59:59', 0, NULL, 1200, 480),
(1, 'Organize Home Office Space', 'Declutter and reorganize home office for better productivity. Install new shelving, cable management system, and ergonomic chair. Set up proper lighting and create filing system for documents.', 'pending', 'low', 0, '2025-11-30 17:00:00', 0, NULL, 360, 0),
(1, 'Prepare Tax Documents', 'Gather all necessary documents for 2025 tax filing including W-2s, 1099s, receipts for deductions, mortgage statements, and charitable contribution records. Schedule appointment with tax preparer.', 'pending', 'high', 0, '2026-01-31 23:59:59', 1, 'yearly', 300, 0),
(1, 'Plan Weekend Hiking Trip', 'Research and plan 2-day hiking trip to Blue Ridge Mountains. Book accommodation, check weather forecast, prepare gear list, and plan meals. Invite 3 friends and coordinate transportation.', 'pending', 'low', 0, '2025-10-25 08:00:00', 0, NULL, 120, 0),
(1, 'Review and Update Emergency Contacts', 'Update emergency contact information in phone, workplace records, and medical forms. Include family members, close friends, and healthcare providers. Share updated list with family members.', 'completed', 'medium', 100, '2025-10-12 14:00:00', 1, 'yearly', 60, 45);

-- Insert note-tag relationships
INSERT INTO `note_tags` (`note_id`, `tag_id`) VALUES
(1, 1), (1, 10), -- Project Planning Meeting Notes -> Work, Projects
(2, 3), (2, 2), -- Healthy Recipe Collection -> Health, Personal
(3, 4), (3, 2), -- Investment Portfolio Review -> Finance, Personal
(4, 6), (4, 2), -- Travel Itinerary -> Travel, Personal
(5, 5), (5, 1), (5, 10), -- Learning Path -> Learning, Work, Projects
(6, 8), (6, 2), -- Home Renovation -> Home, Personal
(7, 9), (7, 2), -- Family Reunion -> Family, Personal
(8, 7), (8, 2), -- Shopping List -> Shopping, Personal
(9, 3), (9, 2), -- Health & Fitness -> Health, Personal
(10, 5), (10, 2); -- Book Reading List -> Learning, Personal

-- Insert task-tag relationships
INSERT INTO `task_tags` (`task_id`, `tag_id`) VALUES
(1, 1), (1, 10), -- Q4 Project Proposal -> Work, Projects
(2, 3), (2, 2), -- Health Checkup -> Health, Personal
(3, 4), (3, 2), -- Car Insurance -> Finance, Personal
(4, 9), (4, 2), -- Thanksgiving Dinner -> Family, Personal
(5, 1), (5, 5), -- LinkedIn Profile -> Work, Learning
(6, 5), (6, 2), -- Learn Spanish -> Learning, Personal
(7, 8), (7, 2), -- Organize Office -> Home, Personal
(8, 4), (8, 2), -- Tax Documents -> Finance, Personal
(9, 6), (9, 2), -- Hiking Trip -> Travel, Personal
(10, 9), (10, 2); -- Emergency Contacts -> Family, Personal

-- Insert sample subtasks for some tasks
INSERT INTO `subtasks` (`task_id`, `title`, `is_completed`, `sort_order`) VALUES
(1, 'Research market trends and competitor analysis', 1, 1),
(1, 'Create project timeline with milestones', 1, 2),
(1, 'Estimate budget and resource requirements', 1, 3),
(1, 'Identify potential risks and mitigation strategies', 0, 4),
(1, 'Prepare presentation slides', 0, 5),
(1, 'Schedule stakeholder review meeting', 0, 6),
(6, 'Complete Duolingo daily lessons (5 lessons/day)', 0, 1),
(6, 'Practice speaking with native speakers (2x/week)', 0, 2),
(6, 'Review past tense conjugations', 1, 3),
(6, 'Study future tense conjugations', 0, 4),
(6, 'Take A2 level assessment test', 0, 5),
(7, 'Declutter desk and filing cabinets', 0, 1),
(7, 'Install new shelving units', 0, 2),
(7, 'Set up cable management system', 0, 3),
(7, 'Purchase and install ergonomic chair', 0, 4),
(7, 'Install proper lighting fixtures', 0, 5),
(7, 'Create digital filing system', 0, 6);

-- Insert sample task reminders
INSERT INTO `task_reminders` (`task_id`, `reminder_time`, `reminder_type`, `is_sent`) VALUES
(1, '2025-10-19 09:00:00', 'email', 0),
(1, '2025-10-20 08:00:00', 'push', 0),
(2, '2025-11-10 10:00:00', 'email', 0),
(2, '2025-11-14 18:00:00', 'push', 0),
(3, '2025-12-05 09:00:00', 'email', 0),
(4, '2025-11-15 10:00:00', 'email', 0),
(6, '2025-10-15 20:00:00', 'push', 0),
(8, '2026-01-15 09:00:00', 'email', 0),
(9, '2025-10-20 18:00:00', 'push', 0);

-- Insert sample note versions (version history)
INSERT INTO `note_versions` (`note_id`, `version_number`, `title`, `content`, `change_summary`) VALUES
(1, 1, 'Project Planning Meeting Notes', 'Initial version with basic meeting notes', 'Initial creation'),
(1, 2, 'Project Planning Meeting Notes', 'Added detailed timeline and resource allocation', 'Enhanced with timeline details'),
(1, 3, 'Project Planning Meeting Notes', 'Added next meeting schedule and action items', 'Added follow-up information'),
(3, 1, 'Investment Portfolio Review', 'Basic portfolio overview', 'Initial creation'),
(3, 2, 'Investment Portfolio Review', 'Added performance metrics and analysis', 'Enhanced with performance data'),
(5, 1, 'Learning Path - Machine Learning', 'Basic learning outline', 'Initial creation'),
(5, 2, 'Learning Path - Machine Learning', 'Added detailed weekly breakdown', 'Enhanced with detailed schedule'),
(5, 3, 'Learning Path - Machine Learning', 'Added project goals and resources', 'Added project planning');

-- Insert sample user preferences
INSERT INTO `user_preferences` (`user_id`, `theme`, `language`, `timezone`, `date_format`, `time_format`, `notifications_email`, `notifications_push`, `notifications_sound`, `auto_save_notes`, `auto_save_interval`, `default_note_color`, `default_task_priority`) VALUES
(1, 'light', 'en', 'America/New_York', 'Y-m-d', '12h', 1, 1, 1, 1, 30, '#ffffff', 'medium');

-- Insert sample audit logs
INSERT INTO `audit_logs` (`user_id`, `action`, `resource_type`, `resource_id`, `ip_address`, `user_agent`, `metadata`) VALUES
(1, 'note_created', 'note', 1, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"title": "Project Planning Meeting Notes", "priority": "high"}'),
(1, 'note_updated', 'note', 1, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"changes": ["timeline", "resources"]}'),
(1, 'task_created', 'task', 1, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"title": "Complete Q4 Project Proposal", "priority": "urgent"}'),
(1, 'task_completed', 'task', 5, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"completion_time": "2025-10-10 16:00:00"}'),
(1, 'tag_created', 'tag', 1, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"name": "Work", "color": "#3b82f6"}'),
(1, 'note_tagged', 'note', 2, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"tag_id": 3, "tag_name": "Health"}'),
(1, 'task_tagged', 'task', 2, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"tag_id": 3, "tag_name": "Health"}'),
(1, 'subtask_created', 'subtask', 1, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"task_id": 1, "title": "Research market trends"}'),
(1, 'reminder_set', 'task', 1, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"reminder_time": "2025-10-19 09:00:00", "type": "email"}'),
(1, 'preferences_updated', 'user_preferences', 1, '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', '{"theme": "light", "notifications": true}');

-- Insert sample AI suggestions (for future AI features)
INSERT INTO `ai_suggestions` (`user_id`, `suggestion_type`, `content`, `metadata`, `is_accepted`) VALUES
(1, 'note_summary', 'Consider adding a timeline visualization to your project planning notes for better clarity.', '{"note_id": 1, "confidence": 0.85}', NULL),
(1, 'task_priority', 'Your health checkup task might benefit from being scheduled earlier given your current workload.', '{"task_id": 2, "current_priority": "medium", "suggested_priority": "high"}', NULL),
(1, 'content_suggestion', 'Your investment portfolio could benefit from adding REITs for better diversification.', '{"note_id": 3, "context": "portfolio_review"}', 1),
(1, 'task_priority', 'The Spanish learning task has been in progress for a while. Consider breaking it into smaller milestones.', '{"task_id": 6, "suggestion": "break_into_milestones"}', NULL),
(1, 'content_suggestion', 'Your travel itinerary could include backup plans for weather contingencies.', '{"note_id": 4, "context": "travel_planning"}', NULL);

-- Insert sample backup logs
INSERT INTO `backup_logs` (`backup_type`, `backup_size`, `backup_path`, `status`, `error_message`) VALUES
('automatic', 15728640, '/backups/secure_notes_2025_10_14_auto.sql', 'success', NULL),
('manual', 16384000, '/backups/secure_notes_2025_10_13_manual.sql', 'success', NULL),
('automatic', 15204352, '/backups/secure_notes_2025_10_12_auto.sql', 'success', NULL),
('automatic', 0, '/backups/secure_notes_2025_10_11_auto.sql', 'failed', 'Disk space insufficient'),
('manual', 14548992, '/backups/secure_notes_2025_10_10_manual.sql', 'success', NULL);

-- Insert sample rate limits (for demonstration)
INSERT INTO `rate_limits` (`ip_address`, `endpoint`, `request_count`, `window_start`) VALUES
('192.168.1.100', '/api/notes', 45, '2025-10-14 17:00:00'),
('192.168.1.100', '/api/tasks', 32, '2025-10-14 17:00:00'),
('192.168.1.100', '/api/tags', 8, '2025-10-14 17:00:00'),
('192.168.1.101', '/api/notes', 12, '2025-10-14 17:00:00'),
('192.168.1.102', '/api/tasks', 28, '2025-10-14 17:00:00');

-- Update tag usage counts based on relationships
UPDATE `tags` SET `usage_count` = (
    SELECT COUNT(*) FROM (
        SELECT note_id FROM `note_tags` WHERE tag_id = tags.id
        UNION ALL
        SELECT task_id FROM `task_tags` WHERE tag_id = tags.id
    ) AS combined_usage
) WHERE id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

-- Insert sample user sessions (for session management)
INSERT INTO `user_sessions` (`user_id`, `session_id`, `ip_address`, `user_agent`, `device_info`, `is_active`, `expires_at`) VALUES
(1, CONCAT('sess_', UNIX_TIMESTAMP(), '_', RAND()), '192.168.1.100', 'Mozilla/5.0 Windows NT 10.0 Win64 x64 AppleWebKit/537.36', 'Windows 10 Chrome 118', 1, DATE_ADD(NOW(), INTERVAL 2 HOUR)),
(1, CONCAT('sess_', UNIX_TIMESTAMP(), '_', RAND()), '192.168.1.100', 'Mozilla/5.0 iPhone CPU iPhone OS 17_0 like Mac OS X', 'iPhone 15 Safari 17', 1, DATE_ADD(NOW(), INTERVAL 2 HOUR));

-- Insert sample two-factor codes (for 2FA demonstration)
INSERT INTO `two_factor_codes` (`user_id`, `code`, `type`, `expires_at`) VALUES
(1, '123456', 'email', DATE_ADD(NOW(), INTERVAL 10 MINUTE)),
(1, '789012', 'sms', DATE_ADD(NOW(), INTERVAL 10 MINUTE));

-- Insert sample password reset tokens (for password reset demonstration)
INSERT INTO `password_reset_tokens` (`user_id`, `token`, `expires_at`) VALUES
(1, CONCAT('reset_', MD5(CONCAT(RAND(), NOW()))), DATE_ADD(NOW(), INTERVAL 1 HOUR));

-- Insert sample email verification tokens (for email verification demonstration)
INSERT INTO `email_verifications` (`user_id`, `token`, `expires_at`) VALUES
(1, CONCAT('verify_', MD5(CONCAT(RAND(), NOW()))), DATE_ADD(NOW(), INTERVAL 24 HOUR));

COMMIT;
