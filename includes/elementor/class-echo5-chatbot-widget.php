<?php

class Echo5_Chatbot_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'echo5_chatbot';
    }

    public function get_title() {
        return __('Echo5 Chatbot', 'echo5-ai-chatbot');
    }

    public function get_icon() {
        return 'eicon-chat';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Chatbot Settings', 'echo5-ai-chatbot'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'chat_height',
            [
                'label' => __('Chat Height', 'echo5-ai-chatbot'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh'],
                'range' => [
                    'px' => ['min' => 300, 'max' => 1000],
                    'vh' => ['min' => 30, 'max' => 100],
                ],
                'default' => ['unit' => 'px', 'size' => 500],
            ]
        );

        $this->add_control(
            'chat_width',
            [
                'label' => __('Chat Width', 'echo5-ai-chatbot'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => 300, 'max' => 1000],
                    '%' => ['min' => 30, 'max' => 100],
                ],
                'default' => ['unit' => 'px', 'size' => 400],
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Chat Style', 'echo5-ai-chatbot'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background',
                'label' => __('Background', 'echo5-ai-chatbot'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .echo5-chat-container',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border',
                'label' => __('Border', 'echo5-ai-chatbot'),
                'selector' => '{{WRAPPER}} .echo5-chat-container',
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'echo5-ai-chatbot'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .echo5-chat-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $custom_style = sprintf(
            'style="height: %s%s; width: %s%s;"',
            $settings['chat_height']['size'],
            $settings['chat_height']['unit'],
            $settings['chat_width']['size'],
            $settings['chat_width']['unit']
        );
        ?>
        <div class="echo5-elementor-chat-wrapper" <?php echo $custom_style; ?>>
            <?php echo do_shortcode('[echo5_chatbot]'); ?>
        </div>
        <?php
    }
}
