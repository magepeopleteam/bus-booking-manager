<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	//echo '<pre>';print_r();echo '</pre>';
	if ( ! class_exists( 'MP_SUPER_SLIDER' ) ) {
		class MP_SUPER_SLIDER {
			public function __construct() {
				add_action( 'wp_enqueue_scripts', array( $this, 'super_slider_script' ), 90 );
				add_filter( 'wbbm_settings_sec_reg', array( $this, 'slider_tab_name' ), 20 );
				add_filter( 'wbbm_settings_sec_fields', array( $this, 'slider_settings' ), 10 );
				//add_shortcode('add_super_slider', array($this, 'super_slider'), 10, 2);
				add_action( 'add_super_slider', array( $this, 'super_slider' ), 10, 2);
				add_action( 'add_super_slider_only', array( $this, 'super_slider_only' ), 10, 1 );
				add_action( 'add_super_slider_icon_indicator', array( $this, 'icon_indicator' ), 10 );
			}
			public function super_slider_script() {
				wp_enqueue_style( 'super_slider_style', plugin_dir_url( __DIR__ ) . 'super_slider/super_slider.css', array(), time() );
				wp_enqueue_script( 'super_slider_script', plugin_dir_url( __DIR__ ) . 'super_slider/super_slider.js', array(), time(), true );
			}
			public function slider_tab_name( $default_sec ): array {
				$sections = array(
					array(
						'id'    => 'super_slider_settings',
						'title' => __( 'Super Slider Settings', 'tour-booking-manager' )
					)
				);
				return array_merge( $default_sec, $sections );
			}
			public function slider_settings( $default_fields ): array {
				$settings_fields = array(
					'super_slider_settings' => array(
						array(
							'name'    => 'super_slider_type',
							'label'   => esc_html__( 'Slider Type', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please Select Slider Type Default Slider', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'slider',
							'options' => array(
								'slider'       => esc_html__( 'Slider', 'tour-booking-manager' ),
								'single_image' => esc_html__( 'Post Thumbnail', 'tour-booking-manager' )
							)
						),
						array(
							'name'    => 'super_slider_style',
							'label'   => esc_html__( 'Slider Style', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please Select Slider Style Default Style One', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'style_1',
							'options' => array(
								'style_1' => esc_html__( 'Style One', 'tour-booking-manager' ),
								'style_2' => esc_html__( 'Style Two', 'tour-booking-manager' ),
							)
						),
						array(
							'name'    => 'super_slider_indicator_visible',
							'label'   => esc_html__( 'Slider Indicator Visible?', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please Select Slider Indicator Visible or Not? Default ON', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'tour-booking-manager' ),
								'off' => esc_html__( 'Off', 'tour-booking-manager' )
							)
						),
						array(
							'name'    => 'super_slider_indicator_type',
							'label'   => esc_html__( 'Slider Indicator Type', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please Select Slider Indicator Type Default Icon', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'icon',
							'options' => array(
								'icon'  => esc_html__( 'Icon Indicator', 'tour-booking-manager' ),
								'image' => esc_html__( 'image Indicator', 'tour-booking-manager' )
							)
						),
						array(
							'name'    => 'super_slider_showcase_visible',
							'label'   => esc_html__( 'Slider Showcase Visible?', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please Select Slider Showcase Visible or Not? Default ON', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'tour-booking-manager' ),
								'off' => esc_html__( 'Off', 'tour-booking-manager' )
							)
						),
						array(
							'name'    => 'super_slider_showcase_position',
							'label'   => esc_html__( 'Slider Showcase Position', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please Select Slider Showcase Position Default Right', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'right',
							'options' => array(
								'top'    => esc_html__( 'At Top Position', 'tour-booking-manager' ),
								'right'  => esc_html__( 'At Right Position', 'tour-booking-manager' ),
								'bottom' => esc_html__( 'At Bottom Position', 'tour-booking-manager' ),
								'left'   => esc_html__( 'At Left Position', 'tour-booking-manager' )
							)
						),
						array(
							'name'    => 'super_slider_popup_image_indicator',
							'label'   => esc_html__( 'Slider Popup Image Indicator', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please Select Slider Popup Indicator Image ON or Off? Default ON', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'tour-booking-manager' ),
								'off' => esc_html__( 'Off', 'tour-booking-manager' )
							)
						),
						array(
							'name'    => 'super_slider_popup_icon_indicator',
							'label'   => esc_html__( 'Slider Popup Icon Indicator', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please Select Slider Popup Indicator Icon ON or Off? Default ON', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'tour-booking-manager' ),
								'off' => esc_html__( 'Off', 'tour-booking-manager' )
							)
						)
					)
				);
				return array_merge( $default_fields, $settings_fields );
			}
			public function super_slider( $post_id = '' ,$meta_key='') {
				$type      = self::get_settings( 'super_slider_type', 'super_slider_settings', 'slider' );
				$post_id   = $post_id > 0 ? $post_id : get_the_id();
				$image_ids = $this->get_slider_ids( $post_id, $meta_key);
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					if ( $type == 'slider' && sizeof( $image_ids ) > 1 ) {
						$this->slider( $post_id, $image_ids );
					} else {
						$this->post_thumbnail( $image_ids[0] );
					}
				} else {
					$this->post_thumbnail();
				}
			}
			public function super_slider_only( $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
					<div class="superSlider placeholder_area">
						<?php $this->slider_all_item( $image_ids ); ?>
					</div>
					<?php
				}
			}
			public function slider( $post_id, $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$showcase_position = self::get_settings( 'super_slider_showcase_position', 'super_slider_settings', 'right' );
					$column_class      = $showcase_position == 'top' || $showcase_position == 'bottom' ? 'area_column' : '';
					$slider_style      = self::get_settings( 'super_slider_style', 'super_slider_settings', 'style_1' );
					?>
					<div class="superSlider placeholder_area fdColumn">
						<div class="dFlex  <?php echo esc_attr( $column_class ); ?>">
							<?php
								if ( $showcase_position == 'top' || $showcase_position == 'left' ) {
									$this->slider_showcase( $image_ids );
								}
								$this->slider_all_item( $image_ids );
								if ( $showcase_position == 'bottom' || $showcase_position == 'right' ) {
									$this->slider_showcase( $image_ids );
								}
								if ( $slider_style == 'style_2' ) {
									?>
									<div class="abTopLeft">
										<button type="button" class="_dButton_bgWhite_textDefault" data-target-popup="superSlider" data-slide-index="1">
											<?php echo esc_html__( 'View All', 'tour-booking-manager' ) . ' ' . sizeof( $image_ids ) . ' ' . esc_html__( 'Images', 'tour-booking-manager' ); ?>
										</button>
									</div>
									<?php
								}
							?>
						</div>
						<?php
							$slider_indicator = self::get_settings( 'super_slider_indicator_visible', 'super_slider_settings', 'on' );
							$icon             = self::get_settings( 'super_slider_indicator_type', 'super_slider_settings', 'icon' );
							if ( $slider_indicator == 'on' && $icon == 'image' ) {
								$this->image_indicator( $image_ids );
							}
						?>
						<?php $this->slider_popup( $post_id, $image_ids ); ?>
					</div>
					<?php
				}
			}
			public function post_thumbnail( $image_id = '' ) {
				$thumbnail = self::get_image_url( '', $image_id );
				if ( $thumbnail ) {
					?>
					<div class="superSlider">
						<div data-bg-image="<?php echo esc_html( $thumbnail ); ?>"></div>
					</div>
					<?php
				}
			}
			public function slider_all_item( $image_ids, $popup_slider_icon = '' ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
					<div class="sliderAllItem">
						<?php
							$count = 1;
							foreach ( $image_ids as $id ) {
								$image_url = self::get_image_url( '', $id );
								?>
								<div class="sliderItem" data-slide-index="<?php echo esc_html( $count ); ?>" data-target-popup="superSlider" data-placeholder>
									<div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
								</div>
								<?php
								$count ++;
							}
						?>
						<?php
							$icon = self::get_settings( 'super_slider_indicator_type', 'super_slider_settings', 'icon' );
							if ( ( $icon == 'icon' || $popup_slider_icon == 'on' ) && sizeof( $image_ids ) > 1 ) {
								$this->icon_indicator( $popup_slider_icon );
							}
						?>
					</div>
					<?php
				}
			}
			public function slider_showcase( $image_ids ) {
				$showcase = self::get_settings( 'super_slider_showcase_visible', 'super_slider_settings', 'on' );
				if ( $showcase == 'on' && is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$showcase_position = self::get_settings( 'super_slider_showcase_position', 'super_slider_settings', 'right' );
					$slider_style      = self::get_settings( 'super_slider_style', 'super_slider_settings', 'style_1' );
					?>
					<div class="sliderShowcase <?php echo esc_html( $showcase_position . ' ' . $slider_style ); ?>">
						<?php
							if ( $slider_style == 'style_1' ) {
								$this->slider_showcase_style_1( $image_ids );
							} else {
								$this->slider_showcase_style_2( $image_ids );
							}
						?>
					</div>
					<?php
				}
			}
			public function slider_showcase_style_1( $image_ids ) {
				$count = 1;
				foreach ( $image_ids as $id ) {
					$image_url = self::get_image_url( '', $id );
					if ( $count < 4 ) {
						?>
						<div class="sliderShowcaseItem" data-slide-target="<?php echo esc_html( $count ); ?>" data-placeholder>
							<div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
						</div>
						<?php
					}
					if ( $count == 4 ) {
						?>
						<div class="sliderShowcaseItem" data-target-popup="superSlider" data-placeholder>
							<div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
							<div class="sliderMoreItem">
								<span class="fas fa-plus"></span>
								<?php echo sizeof( $image_ids ) - 4; ?>
								<span class="far fa-image"></span>
							</div>
						</div>
						<?php
					}
					$count ++;
				}
			}
			public function slider_showcase_style_2( $image_ids ) {
				$count = 1;
				foreach ( $image_ids as $id ) {
					$image_url = self::get_image_url( '', $id );
					if ( $count > 1 && $count < 5 ) {
						?>
						<div class="sliderShowcaseItem" data-target-popup="superSlider" data-slide-index="<?php echo esc_html( $count ); ?>" data-placeholder>
							<div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
						</div>
						<?php
					}
					$count ++;
				}
			}
			public function image_indicator( $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
					<div class="slideIndicator">
						<?php
							$count = 1;
							foreach ( $image_ids as $id ) {
								$image_url = self::get_image_url( '', $id, array( 150, 100 ) );
								?>
								<div class="slideIndicatorItem" data-slide-target="<?php echo esc_html( $count ); ?>">
									<div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
								</div>
								<?php
								$count ++;
							}
						?>
					</div>
					<?php
				}
			}
			public function icon_indicator( $popup_slider_icon = '' ) {
				$slider_indicator = self::get_settings( 'super_slider_indicator_visible', 'super_slider_settings', 'on' );
				if ( $slider_indicator == 'on' || $popup_slider_icon == 'on' ) {
					?>
					<div class="iconIndicator prevItem">
						<span class="fas fa-chevron-circle-left"></span>
					</div>
					<div class="iconIndicator nextItem">
						<span class="fas fa-chevron-circle-right"></span>
					</div>
					<?php
				}
			}
			public function slider_popup( $post_id, $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$popup_icon_indicator = self::get_settings( 'super_slider_popup_icon_indicator', 'super_slider_settings', 'on' );
					?>
					<div class="sliderPopup" data-popup="superSlider">
						<div class="superSlider">
							<div class="popupHeader">
								<h2><?php echo get_the_title( $post_id ); ?></h2>
								<span class="fas fa-times popupClose"></span>
							</div>
							<div class="popupBody">
								<?php $this->slider_all_item( $image_ids, $popup_icon_indicator ); ?>
							</div>
							<div class="popupFooter">
								<?php
									$indicator = self::get_settings( 'super_slider_popup_image_indicator', 'super_slider_settings', 'on' );
									if ( $indicator == 'on' ) {
										$this->image_indicator( $image_ids );
									}
								?>
							</div>
						</div>
					</div>
					<?php
				}
			}
			//==============//
			public function get_slider_ids( $post_id, $key ) {
				$thumb_id  = get_post_thumbnail_id( $post_id );
				$image_ids = self::get_post_info( $post_id, $key, array() );
				if ( $thumb_id ) {
					array_unshift( $image_ids, $thumb_id );
				}
				return array_unique( $image_ids );
			}
            public static function get_post_info( $tour_id, $key, $default = '' ) {
                $data = get_post_meta( $tour_id, $key, true ) ?: $default;
                return self::data_sanitize( $data );
            }
            public static function data_sanitize( $data ) {
                $data = maybe_unserialize( $data );
                if ( is_string( $data ) ) {
                    $data = maybe_unserialize( $data );
                    if ( is_array( $data ) ) {
                        $data = self::data_sanitize( $data );
                    } else {
                        $data = sanitize_text_field( $data );
                    }
                } elseif ( is_array( $data ) ) {
                    foreach ( $data as &$value ) {
                        if ( is_array( $value ) ) {
                            $value = self::data_sanitize( $value );
                        } else {
                            $value = sanitize_text_field( $value );
                        }
                    }
                }
                return $data;
            }
            public static function get_image_url( $post_id = '', $image_id = '', $size = 'full' ) {
                if ( $post_id ) {
                    $image_id = self::get_post_info( $post_id, 'ttbm_list_thumbnail' );
                    $image_id = $image_id ?: get_post_thumbnail_id( $post_id );
                }
                return wp_get_attachment_image_url( $image_id, $size );
            }
            public static function get_settings( $key, $option_name, $default = '' ) {
                $options = get_option( $option_name );
                return self::get_ttbm_settings( $options, $key, $default );
            }
            public static function get_ttbm_settings( $options, $key, $default = '' ) {
                if ( isset( $options[ $key ] ) && $options[ $key ] ) {
                    $default = $options[ $key ];
                }
                return $default;
            }
		}
		new MP_SUPER_SLIDER();
	}