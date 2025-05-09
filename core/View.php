<?php
namespace Core;

/**
 * View Class
 * Handles view rendering and layout management
 */
class View {
    protected $layout = 'layouts/main';
    protected $viewData = [];
    
    /**
     * Set the layout to use
     * 
     * @param string $layout The layout name
     * @return View
     */
    public function setLayout($layout) {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Add data to be passed to the view
     * 
     * @param string $key The data key
     * @param mixed $value The data value
     * @return View
     */
    public function with($key, $value) {
        $this->viewData[$key] = $value;
        return $this;
    }
    
    /**
     * Get the path to a view file
     * 
     * @param string $view The view name
     * @return string The full path to the view file
     */
    protected function getViewPath($view) {
        return APP_ROOT . '/app/Views/' . $view . '.php';
    }
    
    /**
     * Render a view
     * 
     * @param string $view The view to render
     * @param array $data Data to pass to the view
     * @param bool $useLayout Whether to use a layout
     * @return void
     */
    public function render($view, $data = [], $useLayout = true) {
        // Merge passed data with view data
        $data = array_merge($this->viewData, $data);
        
        // Extract variables for the view
        extract($data);
        
        // Get view content
        ob_start();
        $viewPath = $this->getViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found at {$viewPath}");
        }
        
        include $viewPath;
        $content = ob_get_clean();
        
        // Render with layout or directly
        if ($useLayout) {
            $layoutPath = $this->getViewPath($this->layout);
            
            if (!file_exists($layoutPath)) {
                throw new \Exception("Layout {$this->layout} not found");
            }
            
            include $layoutPath;
        } else {
            echo $content;
        }
    }
    
    /**
     * Render a partial view
     * 
     * @param string $partial The partial view to render
     * @param array $data Data to pass to the partial
     * @return void
     */
    public function partial($partial, $data = []) {
        extract($data);
        include $this->getViewPath('partials/' . $partial);
    }
    
    /**
     * Include a component
     * 
     * @param string $name The component name
     * @param array $data Component data
     * @return void
     */
    public function component($name, $data = []) {
        $this->partial('_' . $name, $data);
    }
}