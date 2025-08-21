# 🍽️ EasyPrep - Smart Meal Planning & Recipe Management

A modern, comprehensive meal planning and recipe management web application built with PHP and modern web technologies, designed for food lovers and home cooks.

## ✨ Features

### 🥘 **For Food Enthusiasts**
- **Recipe Discovery**: Browse through curated Bengali and international cuisine recipes
- **Advanced Search**: Find recipes by ingredients, cuisine type, cooking time, and dietary preferences
- **Recipe Collection**: Save and organize your favorite recipes
- **Nutritional Tracking**: Monitor calories, macros, and dietary restrictions
- **External Recipe Integration**: Access recipes from Spoonacular and TheMealDB APIs

### 📅 **For Meal Planners**
- **Weekly Meal Plans**: Create and organize weekly meal schedules
- **Customizable Plans**: Set serving sizes, calorie targets, and dietary preferences
- **Plan Templates**: Save and reuse successful meal plans
- **Visual Planning Interface**: Intuitive meal planning experience
- **Receipt-Style Display**: Beautiful, printable meal plan layouts

### 🛒 **For Grocery Shoppers**
- **Smart Grocery Lists**: Automatically generate shopping lists from meal plans
- **Product Search**: Find grocery items with integrated product database
- **List Organization**: Categorize items by store sections or meal types
- **Shopping History**: Track your grocery shopping patterns

### 👤 **For Users**
- **Personalized Dashboard**: Overview of your meal planning activity and statistics
- **User Profiles**: Manage your preferences and dietary restrictions
- **Responsive Design**: Mobile-friendly interface that works on all devices
- **Modern UI/UX**: Clean, intuitive design with smooth animations

## 🛠️ Tech Stack

### **Frontend**
- **HTML5 & CSS3**: Modern, responsive web interface
- **JavaScript (ES6+)**: Interactive features and dynamic content
- **Font Awesome**: Icon library for enhanced visual experience
- **Google Fonts**: Typography optimization (Inter, Poppins, Nunito)

### **Backend**
- **PHP 7.4+**: Core application logic and server-side processing
- **MySQL**: Database management for users, recipes, and meal plans
- **RESTful APIs**: Integration with external recipe and grocery services

### **APIs & Services**
- **Spoonacular API**: Recipe discovery and nutritional information
- **TheMealDB API**: Additional recipe database (free tier)
- **Grocery Product APIs**: Product search and pricing information

## 🚀 Getting Started

### **Prerequisites**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (for dependency management)

### **Installation**

1. **Clone the repository:**
   ```bash
   git clone https://github.com/sumiyaa-akterr/easyPrep.git
   cd easyPrep
   ```

2. **Configure Database:**
   - Create a MySQL database
   - Update `config.php` with your database credentials
   - Run `create_tables.php` to set up the database schema

3. **API Configuration:**
   - Get API keys from [Spoonacular](https://spoonacular.com/food-api)
   - Update API keys in `config.php`

4. **Web Server Configuration:**
   - Point your web server to the project directory
   - Ensure PHP has write permissions for session management

5. **Access the Application:**
   - Navigate to your web server URL
   - Create an account and start meal planning!

## 📁 Project Structure

```
easyprep/
├── config.php              # Configuration and API keys
├── connect.php             # Database connection
├── create_tables.php       # Database schema setup
├── dashboard.php           # Main user dashboard
├── meal-plan.php          # Meal planning interface
├── recipes.php            # Recipe browsing and search
├── recipe-detail.php      # Individual recipe view
├── grocery.php            # Grocery management
├── profile.php            # User profile management
├── login.php              # User authentication
├── logout.php             # Session management
├── styles/                # CSS stylesheets
│   ├── dashboard.css      # Dashboard styling
│   ├── meal-plan.css      # Meal planner styling
│   ├── recipes.css        # Recipe pages styling
│   └── homepage.css       # Landing page styling
├── scripts/               # JavaScript files
│   ├── dashboard.js       # Dashboard functionality
│   ├── meal-plan.js       # Meal planner interactions
│   └── recipes.js         # Recipe page functionality
└── images/                # Application assets
    └── easyprep-logo.png  # Brand logo
```

## 🔧 Configuration

### **Database Settings**
```php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'easyprep');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### **API Keys**
```php
// config.php
define('SPOONACULAR_API_KEY', 'your_spoonacular_api_key');
```

## 🎨 Design Features

- **Glass Morphism**: Modern backdrop blur effects
- **Smooth Animations**: CSS-powered transitions and hover effects
- **Responsive Grid**: Mobile-first design approach
- **Color Scheme**: Warm, food-inspired palette with browns and oranges
- **Typography**: Clean, readable font hierarchy
- **Modern UI**: Beautiful, responsive design with smooth animations

## 🌟 Key Benefits

### **For Home Cooks**
- Simplify meal planning and reduce food waste
- Discover new recipes and cuisines
- Organize grocery shopping efficiently
- Track nutritional goals and dietary preferences

### **For Families**
- Plan meals that accommodate different dietary needs
- Coordinate grocery shopping and meal preparation
- Save time and reduce stress around meal decisions
- Build healthy eating habits together

### **For Health-Conscious Users**
- Monitor calorie intake and nutritional balance
- Plan meals around specific dietary restrictions
- Track eating patterns and preferences
- Achieve health and wellness goals

## 🚧 Development Roadmap

### **Phase 1** ✅ (Current)
- Core meal planning functionality
- Recipe management system
- Basic grocery list features
- User authentication and profiles

### **Phase 2** 🚧 (In Progress)
- Advanced meal planning templates
- Recipe sharing and social features
- Enhanced grocery management
- Mobile app development

### **Phase 3** 📋 (Planned)
- AI-powered meal suggestions
- Integration with smart kitchen devices
- Community recipe marketplace
- Advanced analytics and insights

## 🔒 Security Features

- **User Authentication**: Secure login and session management
- **SQL Injection Protection**: Prepared statements for database queries
- **XSS Prevention**: Input sanitization and output encoding
- **Session Security**: Secure cookie handling and session validation

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Commit your changes** (`git commit -m 'Add amazing feature'`)
4. **Push to the branch** (`git push origin feature/amazing-feature`)
5. **Open a Pull Request**

### **Development Guidelines**
- Follow PHP PSR-12 coding standards
- Write clear, documented code
- Include tests for new features
- Update documentation as needed

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

Your Name - @yourusername

## 🙏 Acknowledgments

- **Spoonacular API** for recipe data and nutritional information
- **TheMealDB** for additional recipe resources
- **Font Awesome** for beautiful icons
- **Google Fonts** for typography
- **Unsplash** for high-quality food photography

## 📞 Support

- **Issues**: Report bugs and request features on GitHub
- **Discussions**: Join community discussions and share ideas
- **Documentation**: Check our wiki for detailed guides
- **Email**: Contact us at support@easyprep.com

---

**EasyPrep - Making meal planning simple, delicious, and stress-free for everyone! 🍽️✨**
