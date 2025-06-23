<?php
/**
 * AlingAi Pro - 全局辅助函数
 * 
 * @package AlingAi\Pro\Utils
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

if (!function_exists('now')) {
    /**
     * 获取当前时间
     */
    function now(): EnhancedDateTime
    {
        return new EnhancedDateTime();
    }
}

if (!function_exists('today')) {
    /**
     * 获取今天的日期
     */
    function today(): EnhancedDateTime
    {
        return new EnhancedDateTime('today');
    }
}

/**
 * 扩展的 DateTime 类，支持 Laravel 式的方法
 */
class EnhancedDateTime extends \DateTime
{
    public function startOfWeek(): self
    {
        return $this->modify('monday this week');
    }
    
    public function endOfWeek(): self
    {
        return $this->modify('sunday this week 23:59:59');
    }
    
    public function startOfMonth(): self
    {
        return $this->modify('first day of this month 00:00:00');
    }
    
    public function endOfMonth(): self
    {
        return $this->modify('last day of this month 23:59:59');
    }
    
    public function subDays(int $days): self
    {
        return $this->modify("-{$days} days");
    }
    
    public function subHours(int $hours): self
    {
        return $this->modify("-{$hours} hours");
    }
    
    public function subWeeks(int $weeks): self
    {
        return $this->modify("-{$weeks} weeks");
    }
    
    public function subMonths(int $months): self
    {
        return $this->modify("-{$months} months");
    }    public function addDays(int $days): self
    {
        return $this->modify("+{$days} days");
    }
    
    public function addHours(int $hours): self
    {
        return $this->modify("+{$hours} hours");
    }
    
    public function addMinutes(int $minutes): self
    {
        return $this->modify("+{$minutes} minutes");
    }
    
    public function addSeconds(int $seconds): self
    {
        return $this->modify("+{$seconds} seconds");
    }
    
    public function __get(string $name)
    {
        switch ($name) {
            case 'month':
                return (int) $this->format('n');
            case 'year':
                return (int) $this->format('Y');
            case 'day':
                return (int) $this->format('j');
            default:
                throw new \InvalidArgumentException("Property {$name} does not exist");
        }
    }
}
