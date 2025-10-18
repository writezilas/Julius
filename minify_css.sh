#!/bin/bash

# Function to minify CSS files
minify_css() {
    local input_file="$1"
    local temp_file="${input_file}.tmp"
    
    # Use sed and tr to remove comments, extra whitespace, and newlines
    sed '/^\/\*/,/\*\/$/d' "$input_file" | \
    sed 's/\/\*[^*]*\*\+\([^/*][^*]*\*\+\)*\///g' | \
    sed 's/[[:space:]]\+/ /g' | \
    tr -d '\n' | \
    sed 's/ *{ */{/g' | \
    sed 's/ *} */}/g' | \
    sed 's/ *: */:/g' | \
    sed 's/ *; */;/g' | \
    sed 's/ *, */,/g' | \
    sed 's/; *}/}/g' > "$temp_file"
    
    echo "$temp_file"
}

# Get file sizes
get_file_size() {
    stat -f%z "$1" 2>/dev/null || echo "0"
}

echo "Starting CSS minification..."

# Find and minify large CSS files (>50KB)
find "/Applications/XAMPP/xamppfiles/htdocs/Autobidder/public" -name "*.css" -size +50k | while read -r file; do
    # Skip already minified files
    if [[ "$file" == *.min.css ]]; then
        continue
    fi
    
    original_size=$(get_file_size "$file")
    
    echo "Minifying: $file ($(du -h "$file" | cut -f1))"
    
    temp_file=$(minify_css "$file")
    new_size=$(get_file_size "$temp_file")
    
    if [ "$new_size" -gt 0 ] && [ "$new_size" -lt "$original_size" ]; then
        mv "$temp_file" "$file"
        reduction=$(( (original_size - new_size) * 100 / original_size ))
        echo "  ✓ Minified: ${reduction}% reduction"
    else
        rm -f "$temp_file"
        echo "  → No size reduction achieved"
    fi
done

echo "CSS minification completed!"