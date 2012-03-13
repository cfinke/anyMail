<?php

class thread_arc {
	var $exists;
	
	var $id;
	var $thread;
	
	var $selected_key;
	var $selected_circle;
	
	var $image;
	var $image_width;
	var $image_height;
	
	var $circle_width = 16;
	var $circle_border_width = 3;
	var $orientation = "v";
	
	var $map;
	
	function thread_arc($id){
		$this->id = $id;
		$this->thread = new email_thread($this->id);
		
		// Get the values of the current message.
		$this->selected_key = $this->get_selected_key($this->thread->thread);
		$this->selected_circle = $this->get_circle_number($this->selected_key, $this->thread->flat_thread);
		
		// Set up the configuration values for the graphic.
		$this->image_width = round(($this->thread->num_messages * 1.6 * $this->circle_width) + (0.5 * $this->circle_width));
		$this->image_height = $this->image_width;
		
		$this->exists = (function_exists('imagecreate') &&
						 function_exists('imagecolorallocate') &&
						 function_exists('imagearc') &&
						 function_exists('imagefilledellipse') &&
						 function_exists('imagecopy') &&
						 function_exists('imagedestroy') &&
						 function_exists('imagejpeg'));
	}
	
	function export_image(){
		if ($this->thread->num_messages > 1){
			// Set up for checking the biggest top and bottom depths.
			$top_depth = 1;
			$bottom_depth = 1;
			$counter = 0;
			
			// Create a blank image
			$this->image = imagecreate($this->image_width, $this->image_height);
			
			// Set up the colors for the image
			$white = imagecolorallocate($this->image, 255, 255, 255);
			$black = imagecolorallocate($this->image, 0, 0, 0);
			$default_color = imagecolorallocate($this->image, 5, 60, 124);
			$highlight = imagecolorallocate($this->image, 0, 100, 255);
			
			// Run through the messages in chronological order.
			foreach($this->thread->flat_thread as $key => $value){
				// Get the necessary information for making an arc.
				$begin_circle = $counter++;
				$generation = $this->get_generation($key, $this->thread->thread);
				$replies = $this->get_reply_keys($key, $this->thread->thread);
				
				// For each reply, draw an arc.
				if (is_array($replies)){
					foreach($replies as $reply){
						// Get the circle number of the reply.
						$end_circle = $this->get_circle_number($reply, $this->thread->flat_thread);
						
						// Get the number of circles that must be passed.
						$depth = $end_circle - $begin_circle;
						
						// Get the circle over which the arc must be centered.
						$center_at_circle = ($depth / 2) + $begin_circle;
						
						// Set the color for the arc.
						if (($this->selected_circle == $begin_circle) || ($this->selected_circle == $end_circle)) $color = $highlight;
						else $color = $default_color;
						
						// Set the width and height of the arc.
						$arc_h = round($this->circle_width * 1.7) * $depth;
						$arc_w = max(min($arc_h, 80), -96);
						
						// Set the horizontal center of the arc.
						$center_y = round($this->circle_width  * 3 / 4) + ($center_at_circle * ($this->circle_width + 10));
						
						// Determine whether to use the top or bottom.
						if (($generation % 2) == 0){
							// Check for a new biggest top depth.
							if ($depth > $top_depth) $top_depth = $depth;
							
							// Set the vertical center of the arc.
							$center_x = round($this->circle_width  * 3 / 4) + round(($this->image_height - $this->circle_width) / 2);
							
							// Draw the arc.
							imagearc($this->image, $center_x, $center_y, $arc_w, $arc_h, 270, 90, $color);
						}
						else{
							// Check for a new biggest bottom depth.
							if ($depth > $bottom_depth) $bottom_depth = $depth;
							
							// Set the vertical center of the arc.
							$center_x = round(($this->image_height + $this->circle_width) / 2) - round($this->circle_width  * 3 / 4);
							
							// Draw the arc.
							imagearc($this->image, $center_x, $center_y, $arc_w, $arc_h, 90, 270, $color);
						}
					}
				}
			}
			
			// Draw a circle for each message in the thread.
			for ($i = 0; $i < $this->thread->num_messages; $i++){
				// Get the thread_id of the current circle.
				$this_key = $this->get_flat_key($i, $this->thread->flat_thread);
				
				// Determine the color of the circle.
				$color = (in_array($this_key, $this->thread->unseen)) ? $black : $default_color;
				
				// If this is the selected message, set up the highlighting.
				if ($this_key == $this->selected_key){
					$extra_w = 5;
					$extra_i = 5;
					$color = $highlight;
				}
				else{
					$extra_w = 0;
					$extra_i = 0;
				}
				
				// Draw the dark colored circle (the border)
				imagefilledellipse($this->image, ($this->image_height / 2), round($this->circle_width  * 3 / 4) + ($i * ($this->circle_width + 10)), $this->circle_width + $extra_w, $this->circle_width + $extra_w, $color);
				
				// If this message was sent by the user, make it hollow.
				if (in_array($this_key, $this->thread->sent)) imagefilledellipse($this->image, ($this->image_height / 2), round($this->circle_width  * 3 / 4) + ($i * ($this->circle_width + 10)), $this->circle_width - (2 * $this->circle_border_width) + $extra_i, $this->circle_width - (2 * $this->circle_border_width) + $extra_i, $white);
			}
			
			## Crop the picture
			$depth = ($bottom_depth > $top_depth) ? $bottom_depth : $top_depth;
			
			// Determine the narrowest point at which there is data on the image.
			$image_x = round(($this->image_width - 175) / 2);
			// Determine the widest point at which there is data on the image.
			$image_x_bottom = round($this->image_width / 2) + round($this->circle_width * 0.5) + ($depth * $this->circle_width) + 5;
			
			// Get the height of the new image.
			$new_image_width = 175;
			
			// Create a new image to crop the current image.
			$new_image = imagecreate($new_image_width, $this->image_height);
			
			// Copy the pertinent section of the old image to the new image.
			imagecopy($new_image, $this->image, 0, 0, $image_x, 0, $new_image_width, $this->image_height);
			
			// Destory the old image.
			imagedestroy($this->image);
		}
		else{
			$new_image = imagecreate(1,1);
			$white = imagecolorallocate($new_image, 255, 255, 255);
		}
		
		if ($this->orientation == "h") $new_image = image_rotate($new_image, 90);
		
		// Output the new image.
		header("Content-type: image/jpeg");
		imagejpeg($new_image, '', 100);
		
		// Destroy the new image.
		imagedestroy($new_image);
	}
	
	function get_circle_number($key, $flat_thread){
		$counter = 0;
		
		foreach($flat_thread as $key1 => $value){
			if ($key == $key1){
				return $counter;
			}
			else{
				$counter++;
			}
		}
	}
	
	function get_generation($key, $thread){
		$generation = 0;
		foreach($thread as $node){
			if ($node["thread_id"] == $key){
				return $node["generation"];
			}
			else{
				if (is_array($node["sub_thread"])){
					$generation = $this->get_generation($key, $node["sub_thread"]);
					if ($generation != '') break;
				}
			}
		}
		
		return $generation;
	}
	
	function get_reply_keys($key, $thread){
		$keys = array();
		
		foreach($thread as $node){
			if ($node["thread_id"] == $key){
				if (is_array($node["sub_thread"])){
					foreach($node["sub_thread"] as $reply){
						$keys[] = $reply["thread_id"];
					}
				}
				
				return $keys;
			}
			else{
				if (is_array($node["sub_thread"])){
					$keys = $this->get_reply_keys($key, $node["sub_thread"]);
					if (count($keys) > 0) break;
				}
			}
		}
		
		return $keys;
	}
	
	function get_seen($key, $thread){
		foreach($thread as $node){
			if ($node["thread_id"] == $key){
				return $node["seen"];
			}
			else{
				if (is_array($node["sub_thread"])){
					$seen = $this->get_seen($key, $node["sub_thread"]);
					if ($seen != '') break;
				}
			}
		}
		
		return $seen;
	}
	
	function get_selected_key($thread){
		$key = 0;
		
		foreach($thread as $node){
			if (isset($node["selected"]) && ($node["selected"] == true)){
				return $node["thread_id"];
			}
			else{
				if (is_array($node["sub_thread"])){
					$key = $this->get_selected_key($node["sub_thread"]);
					if ($key != '') break;
				}
			}
		}
		
		return $key;
	}
	
	function get_id($key, $thread){
		$id = '';
		
		foreach($thread as $node){
			if ($node["thread_id"] == $key){
				return $node["message_id"];
			}
			else{
				if (is_array($node["sub_thread"])){
					$id = $this->get_id($key, $node["sub_thread"]);
					if ($id != '') break;
				}
			}
		}
		
		return $id;
	}
	
	function get_subject($key, $thread){
		$subject = '';
		
		foreach($thread as $node){
			if ($node["thread_id"] == $key){
				return $node["Subject"];
			}
			else{
				if (is_array($node["sub_thread"])){
					$subject = $this->get_subject($key, $node["sub_thread"]);
					if ($subject != '') break;
				}
			}
		}
		
		return $subject;
	}
	
	function get_flat_key($i, $flat_thread){
		$counter = 0;
		
		foreach($flat_thread as $key => $value){
			if ($i == $counter++){
				return $key;
			}
		}
	}
	
	function get_image_map(){
		if ($this->thread->num_messages > 1){
			$this->map = '<map id="arc_map" name="arc_map">'."\n";
			
			// Draw a circle for each message in the thread.
			for ($i = 0; $i < $this->thread->num_messages; $i++){
				// Get the thread_id of the current circle.
				$this_key = $this->get_flat_key($i, $this->thread->flat_thread);
				$this_id = $this->get_id($this_key, $this->thread->thread);
				
				if ($this_key != $this->selected_key){
					// Create a link to this message.
					
					if ($this->orientation == "h"){
						// Horizontal map
						$this->map .= '<area shape="rect" coords="'.round($this->circle_width * 1.6 * $i).',0,'.round($this->circle_width * 1.6 * ($i + 1)).','.$this->image_height.'" href="javascript:void(0);" onclick="alert();" alt="'.$this->get_subject($this_key, $this->thread->thread).'" />'."\n";
					}
					else{
						// Vertical map
						$this->map .= '<area shape="rect" coords="0,'.round($this->circle_width * 1.6 * $i).',175,'.round($this->circle_width * 1.6 * ($i + 1)).'" href="javascript:void(0);" onclick="alert();" alt="'.$this->get_subject($this_key, $this->thread->thread).'" />'."\n";
					}
				}
			}
			
			$this->map .= '</map>';
		}
		
		return $this->map;
	}
}

function image_rotate($im, $angle)
{
  if ($angle == 180)
  {
   $im = imagerotate($im, $angle, '');
   return $im;
  }
  elseif ($angle == 90 || $angle == 270)
  {
   $im_ = imagecreate(imagesx($im), imagesy($im));
   $im_ = imagerotate($im, -$angle, '');
   return $im_;
  }
}

?>