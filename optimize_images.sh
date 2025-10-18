#!/bin/bash

# Function to compress JPG files
compress_jpg() {
    local input_file="$1"
    local output_file="$2"
    local quality="$3"
    
    # Use sips to compress JPEG with specific quality
    sips -s format jpeg -s formatOptions "$quality" "$input_file" --out "$output_file"
}

# Function to compress PNG files
compress_png() {
    local input_file="$1"
    local output_file="$2"
    
    # Use sips to optimize PNG
    sips -s format png "$input_file" --out "$output_file"
}

# Get file sizes
get_file_size() {
    stat -f%z "$1" 2>/dev/null || echo "0"
}

echo "Starting image optimization..."

# Find and compress large JPG files (>200KB)
find "/Applications/XAMPP/xamppfiles/htdocs/Autobidder/public" -name "*.jpg" -size +200k | while read -r file; do
    original_size=$(get_file_size "$file")
    temp_file="${file}.tmp"
    
    echo "Compressing: $file ($(du -h "$file" | cut -f1))"
    
    # Try different quality levels
    for quality in 70 80 85; do
        compress_jpg "$file" "$temp_file" "$quality"
        new_size=$(get_file_size "$temp_file")
        
        if [ "$new_size" -lt "$original_size" ]; then
            mv "$temp_file" "$file"
            reduction=$(( (original_size - new_size) * 100 / original_size ))
            echo "  ✓ Compressed with quality $quality: ${reduction}% reduction"
            break
        else
            rm -f "$temp_file"
        fi
    done
done

# Find and compress large PNG files (>200KB)
find "/Applications/XAMPP/xamppfiles/htdocs/Autobidder/public" -name "*.png" -size +200k | while read -r file; do
    original_size=$(get_file_size "$file")
    temp_file="${file}.tmp"
    
    echo "Compressing: $file ($(du -h "$file" | cut -f1))"
    
    compress_png "$file" "$temp_file"
    new_size=$(get_file_size "$temp_file")
    
    if [ "$new_size" -lt "$original_size" ]; then
        mv "$temp_file" "$file"
        reduction=$(( (original_size - new_size) * 100 / original_size ))
        echo "  ✓ Compressed: ${reduction}% reduction"
    else
        rm -f "$temp_file"
        echo "  → No size reduction achieved"
    fi
done

echo "Image optimization completed!"