<?php
/**
 * Theme Manager Library
 * Handles theme and mode switching functionality
 */

namespace SchoolAttendance\Libs;

class ThemeManager {
    private static $themes = ['fluent', 'material', 'glassmorphism', 'cyberpunk'];
    private static $modes = ['white', 'light-gray', 'dark-gray', 'black'];
    
    /**
     * Get available themes
     */
    public static function getThemes() {
        return self::$themes;
    }
    
    /**
     * Get available modes
     */
    public static function getModes() {
        return self::$modes;
    }
    
    /**
     * Validate theme name
     */
    public static function isValidTheme($theme) {
        return in_array($theme, self::$themes);
    }
    
    /**
     * Validate mode name
     */
    public static function isValidMode($mode) {
        return in_array($mode, self::$modes);
    }
    
    /**
     * Get current theme from session or settings
     */
    public static function getCurrentTheme() {
        SessionManager::start();
        return $_SESSION['theme'] ?? getSetting('theme', 'fluent');
    }
    
    /**
     * Get current mode from session or settings
     */
    public static function getCurrentMode() {
        SessionManager::start();
        return $_SESSION['mode'] ?? getSetting('mode', 'light');
    }
    
    /**
     * Set theme
     */
    public static function setTheme($theme) {
        if (self::isValidTheme($theme)) {
            SessionManager::set('theme', $theme);
            updateSetting('theme', $theme);
            return true;
        }
        return false;
    }
    
    /**
     * Set mode
     */
    public static function setMode($mode) {
        // Map simple mode names to CSS data-mode values
        $modeMap = [
            'white' => 'light',
            'light-gray' => 'light-gray',
            'dark-gray' => 'dark-gray',
            'black' => 'black'
        ];
        
        if (self::isValidMode($mode)) {
            SessionManager::set('mode', $mode);
            updateSetting('mode', $mode);
            return true;
        }
        return false;
    }
    
    /**
     * Get theme configuration
     */
    public static function getThemeConfig($theme) {
        $configs = [
            'fluent' => [
                'name' => 'Fluent UI',
                'description' => 'Modern Microsoft-inspired design',
                'icon' => '🪟',
                'supports_transparency' => false
            ],
            'material' => [
                'name' => 'Material UI',
                'description' => "Google's Material Design",
                'icon' => '📱',
                'supports_transparency' => false
            ],
            'glassmorphism' => [
                'name' => 'Glassmorphism',
                'description' => 'Frosted glass effect design',
                'icon' => '🔮',
                'supports_transparency' => true
            ],
            'cyberpunk' => [
                'name' => 'Cyberpunk',
                'description' => 'Futuristic neon-themed design',
                'icon' => '🤖',
                'supports_transparency' => false
            ]
        ];
        
        return $configs[$theme] ?? null;
    }
    
    /**
     * Get mode configuration
     */
    public static function getModeConfig($mode) {
        $configs = [
            'white' => [
                'name' => 'White',
                'bg_primary' => '#ffffff',
                'text_primary' => '#1a1a1a',
                'icon' => '☀️'
            ],
            'light-gray' => [
                'name' => 'Light Gray',
                'bg_primary' => '#f5f5f5',
                'text_primary' => '#2b2b2b',
                'icon' => '🌤️'
            ],
            'dark-gray' => [
                'name' => 'Dark Gray',
                'bg_primary' => '#2d2d2d',
                'text_primary' => '#ffffff',
                'icon' => '🌙'
            ],
            'black' => [
                'name' => 'Black',
                'bg_primary' => '#000000',
                'text_primary' => '#ffffff',
                'icon' => '🌑'
            ]
        ];
        
        return $configs[$mode] ?? null;
    }
    
    /**
     * Generate theme switcher HTML
     */
    public static function generateThemeSwitcher() {
        $html = '<div class="theme-switcher">';
        $html .= '<span class="switcher-label">Tema:</span>';
        $html .= '<div class="theme-options">';
        
        foreach (self::$themes as $theme) {
            $config = self::getThemeConfig($theme);
            $current = self::getCurrentTheme();
            $active = $theme === $current ? 'active' : '';
            
            $html .= "<button 
                        class=\"theme-option $active\" 
                        data-theme=\"$theme\"
                        title=\"{$config['name']} - {$config['description']}\">";
            $html .= "<span class=\"theme-icon\">{$config['icon']}</span>";
            $html .= "<span class=\"theme-name\">{$config['name']}</span>";
            $html .= '</button>';
        }
        
        $html .= '</div></div>';
        return $html;
    }
    
    /**
     * Generate mode switcher HTML
     */
    public static function generateModeSwitcher() {
        $html = '<div class="mode-switcher">';
        $html .= '<span class="switcher-label">Mode:</span>';
        $html .= '<div class="mode-options">';
        
        foreach (self::$modes as $mode) {
            $config = self::getModeConfig($mode);
            $current = self::getCurrentMode();
            $active = $mode === $current ? 'active' : '';
            
            $html .= "<button 
                        class=\"mode-option $active\" 
                        data-mode=\"$mode\"
                        title=\"{$config['name']}\"
                        style=\"background-color: {$config['bg_primary']}; color: {$config['text_primary']}; border: 1px solid #ccc;\">";
            $html .= "<span class=\"mode-icon\">{$config['icon']}</span>";
            $html .= "<span class=\"mode-name\">{$config['name']}</span>";
            $html .= '</button>';
        }
        
        $html .= '</div></div>';
        return $html;
    }
    
    /**
     * Reset to default theme and mode
     */
    public static function reset() {
        SessionManager::set('theme', 'fluent');
        SessionManager::set('mode', 'light');
        updateSetting('theme', 'fluent');
        updateSetting('mode', 'light');
    }
}

/**
 * Widget Manager
 * Handles customizable dashboard widgets
 */
class WidgetManager {
    
    /**
     * Get available widget types
     */
    public static function getAvailableWidgets() {
        return [
            'stats' => [
                'name' => 'Statistik',
                'description' => 'Menampilkan statistik dalam kartu',
                'icon' => '📊'
            ],
            'list' => [
                'name' => 'Daftar',
                'description' => 'Menampilkan daftar data',
                'icon' => '📋'
            ],
            'chart' => [
                'name' => 'Grafik',
                'description' => 'Menampilkan grafik/chart',
                'icon' => '📈'
            ],
            'actions' => [
                'name' => 'Aksi Cepat',
                'description' => 'Tombol aksi cepat',
                'icon' => '⚡'
            ],
            'form' => [
                'name' => 'Formulir',
                'description' => 'Formulir input cepat',
                'icon' => '📝'
            ],
            'calendar' => [
                'name' => 'Kalender',
                'description' => 'Kalender absensi',
                'icon' => '📅'
            ]
        ];
    }
    
    /**
     * Get user widgets
     */
    public static function getUserWidgets($userId) {
        $db = DatabaseHelper::getInstance();
        
        $sql = "SELECT * FROM dashboard_widgets 
                WHERE user_id = ? 
                ORDER BY position ASC";
        
        return $db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Add widget for user
     */
    public static function addWidget($userId, $widgetName, $widgetType, $position = 0, $config = null) {
        $db = DatabaseHelper::getInstance();
        
        $data = [
            'user_id' => $userId,
            'widget_name' => $widgetName,
            'widget_type' => $widgetType,
            'position' => $position,
            'is_visible' => 1,
            'config' => $config ? json_encode($config) : null
        ];
        
        return $db->insert('dashboard_widgets', $data);
    }
    
    /**
     * Update widget visibility
     */
    public static function toggleWidgetVisibility($widgetId, $isVisible) {
        $db = DatabaseHelper::getInstance();
        
        return $db->update(
            'dashboard_widgets',
            ['is_visible' => $isVisible ? 1 : 0],
            'id = ?',
            [$widgetId]
        );
    }
    
    /**
     * Update widget position
     */
    public static function updateWidgetPosition($widgetId, $position) {
        $db = DatabaseHelper::getInstance();
        
        return $db->update(
            'dashboard_widgets',
            ['position' => $position],
            'id = ?',
            [$widgetId]
        );
    }
    
    /**
     * Remove widget
     */
    public static function removeWidget($widgetId) {
        $db = DatabaseHelper::getInstance();
        
        return $db->delete('dashboard_widgets', 'id = ?', [$widgetId]);
    }
    
    /**
     * Get visible widgets for user
     */
    public static function getVisibleWidgets($userId) {
        $db = DatabaseHelper::getInstance();
        
        $sql = "SELECT * FROM dashboard_widgets 
                WHERE user_id = ? AND is_visible = 1
                ORDER BY position ASC";
        
        return $db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Render widget HTML
     */
    public static function renderWidget($widget) {
        $widgetTypes = self::getAvailableWidgets();
        $type = $widget['widget_type'];
        
        if (!isset($widgetTypes[$type])) {
            return '<div class="widget-error">Widget type not found</div>';
        }
        
        $config = json_decode($widget['config'], true) ?? [];
        
        ob_start();
        ?>
        <div class="dashboard-widget" data-widget-id="<?php echo $widget['id']; ?>">
            <div class="widget-header">
                <h4><?php echo htmlspecialchars($widgetTypes[$type]['icon']); ?> <?php echo htmlspecialchars($widget['widget_name']); ?></h4>
                <div class="widget-actions">
                    <button class="btn-widget-toggle" data-widget-id="<?php echo $widget['id']; ?>">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-widget-remove" data-widget-id="<?php echo $widget['id']; ?>">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="widget-body" data-widget-type="<?php echo $type; ?>">
                <?php self::renderWidgetContent($type, $config); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render widget content based on type
     */
    private static function renderWidgetContent($type, $config) {
        switch ($type) {
            case 'stats':
                self::renderStatsWidget($config);
                break;
            case 'list':
                self::renderListWidget($config);
                break;
            case 'actions':
                self::renderActionsWidget($config);
                break;
            default:
                echo '<p>Widget content not implemented</p>';
        }
    }
    
    /**
     * Render stats widget
     */
    private static function renderStatsWidget($config) {
        $metric = $config['metric'] ?? 'total_students';
        $label = $config['label'] ?? 'Total';
        $icon = $config['icon'] ?? 'users';
        
        // This would normally fetch real data
        echo "<div class=\"stat-card\">";
        echo "<div class=\"stat-icon primary\"><i class=\"fas fa-$icon\"></i></div>";
        echo "<div class=\"stat-value\">0</div>";
        echo "<div class=\"stat-label\">$label</div>";
        echo "</div>";
    }
    
    /**
     * Render list widget
     */
    private static function renderListWidget($config) {
        $source = $config['source'] ?? 'recent_activity';
        
        echo "<div class=\"widget-list\">";
        echo "<p>Loading data...</p>";
        echo "</div>";
    }
    
    /**
     * Render actions widget
     */
    private static function renderActionsWidget($config) {
        $actions = $config['actions'] ?? [];
        
        echo "<div class=\"quick-actions\">";
        foreach ($actions as $action) {
            echo "<a href=\"{$action['url']}\" class=\"btn btn-primary\">{$action['label']}</a>";
        }
        echo "</div>";
    }
}
