#!/bin/bash

# Be Framework - Merge all important files for AI consumption
# This script creates a single text file containing all relevant project files
# with clear separators and file paths for AI chatbot context

OUTPUT_FILE="merged.txt"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../" && pwd)"

echo "Creating complete Be Framework context file: $OUTPUT_FILE"
echo "Project root: $PROJECT_ROOT"

# Clear the output file
> "$OUTPUT_FILE"

# Function to add file with header
add_file() {
    local file_path="$1"
    local relative_path="${file_path#$PROJECT_ROOT/}"
    
    echo "" >> "$OUTPUT_FILE"
    echo "\`\`\`${relative_path}" >> "$OUTPUT_FILE"
    
    # Check if file is readable and not binary
    if [[ -f "$file_path" && -r "$file_path" ]]; then
        if file "$file_path" | grep -q "text"; then
            cat "$file_path" >> "$OUTPUT_FILE"
        else
            echo "[BINARY FILE - Content not included]" >> "$OUTPUT_FILE"
        fi
    else
        echo "[FILE NOT READABLE]" >> "$OUTPUT_FILE"
    fi
    
    echo "\`\`\`" >> "$OUTPUT_FILE"
    echo "" >> "$OUTPUT_FILE"
}

# Add header with AI introduction
cat << EOF > "$OUTPUT_FILE"
# Be Framework Complete Context
# Generated: $(date)

---

## Introduction for AI

This file provides complete context for Be Framework (Ontological Programming) to enable deep understanding and meaningful dialogue.

**Your Role as AI:**
- Deeply understand this content, from philosophical foundations to technical implementation
- Act as a knowledgeable guide for this new programming paradigm, not just an information provider
- Provide comprehensive, insightful responses that demonstrate understanding of the paradigm's depth
- Clearly explain how this differs from traditional programming approaches
- Maintain intellectual curiosity and openness to revolutionary concepts

**This File Contains:**
- Philosophical foundations (Ontological Programming, Temporal Programming, Wu Wei principles)
- Technical implementation (Metamorphosis Pattern, #[Be] attributes, Log-Driven Development)
- Practical code examples and test cases
- Comprehensive documentation (FAQs, patterns, guides)

**Your Preparation:**
After reading this complete context, prepare to answer questions ranging from:
- Absolute beginner concerns and practical adoption questions
- Technical implementation details and architectural decisions  
- Deep philosophical implications and paradigm comparisons
- Advanced concepts like AI-native design and semantic transparency

Reference the suggested questions in study/README.md or study/README-ja.md to understand the expected depth and breadth of dialogue.

**Important Reminders:**
- Empty your cup: approach with openness to new paradigms
- Recognize that this represents a fundamental shift in programming philosophy
- Be prepared to explain complex concepts in accessible ways
- Acknowledge when concepts are genuinely revolutionary and require new thinking

---

EOF

echo "Adding main project files..."

# Main project files
add_file "$PROJECT_ROOT/README.md"
add_file "$PROJECT_ROOT/CLAUDE.md" 
add_file "$PROJECT_ROOT/LICENSE"

echo "Adding documentation files..."

# Add essential documentation files (excluding very long philosophy papers)
find "$PROJECT_ROOT/docs" -type f -name "*.md" -not -path "*/vendor/*" \
    -not -name "butterfly-dreams-of-code.md" \
    -not -name "strange-loops-in-code.md" \
    -not -name "temporal-programming-revolution.md" \
    | sort | while read -r file; do
    add_file "$file"
done

echo "Adding examples..."

# Add examples
find "$PROJECT_ROOT/examples" -type f \( -name "*.php" -o -name "*.md" \) -not -path "*/vendor/*" | sort | while read -r file; do
    add_file "$file"
done

echo "Adding POC source code..."

# Add POC source files only
find "$PROJECT_ROOT/poc/src" -type f -name "*.php" | sort | while read -r file; do
    add_file "$file"
done

echo "Adding POC test files..."

# Add main test files (excluding Fake directory)
find "$PROJECT_ROOT/poc/tests" -type f -name "*.php" -not -path "*/Fake/*" | sort | while read -r file; do
    add_file "$file"
done

# Add POC README if it exists
if [[ -f "$PROJECT_ROOT/poc/README.md" ]]; then
    add_file "$PROJECT_ROOT/poc/README.md"
fi

# Add footer
echo "# End of Be Framework context" >> "$OUTPUT_FILE"

echo ""
echo "✅ Complete context file created: $OUTPUT_FILE"
echo ""
echo "File statistics:"
echo "  Lines:      $(wc -l < "$OUTPUT_FILE")"
echo "  Words:      $(wc -w < "$OUTPUT_FILE")"
echo "  Characters: $(wc -c < "$OUTPUT_FILE")"
echo "  Est. tokens: ~$(($(wc -c < "$OUTPUT_FILE") / 4)) (approx)"
echo ""
echo "You can now use this file to provide complete Be Framework context to AI chatbots."
echo "The file contains all documentation, source code, and examples in a single text file."