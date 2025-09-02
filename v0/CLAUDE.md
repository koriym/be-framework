# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Be Framework v0 - Production implementation of Ontological Programming paradigm with semantic logging infrastructure.

## Forward Trace Debugging Best Practices

### 🎯 Core Principle: Execute First, Debug Second

**CRITICAL: Always verify the command executes successfully before adding xdebug-debug**

### ✅ Correct Forward Trace Workflow

1. **First: Verify command execution**
   ```bash
   # Step 1: Ensure basic execution works
   php vendor/bin/phpunit tests/SemanticLog/SchemaComplianceTest.php::testOpenContextSchemaCompliance
   ```

2. **Then: Add Forward Trace debugging**
   ```bash
   # Step 2: Only after confirming execution, add debugging
   ./vendor/bin/xdebug-debug --context="Debugging class resolution issues" --break="tests/SemanticLog/SchemaComplianceTest.php:48" --exit-on-break --steps=10 --json -- php vendor/bin/phpunit --filter testOpenContextSchemaCompliance tests/SemanticLog/SchemaComplianceTest.php
   ```

### ❌ Common Forward Trace Mistakes

**Problem: No output from xdebug-debug**
- **Cause**: Base command doesn't execute properly
- **Solution**: Test the PHP command first without xdebug-debug

**Problem: Wrong command syntax**
- **Cause**: Incorrect PHPUnit filter syntax (`::method` vs `--filter method`)
- **Solution**: Verify command syntax independently

**Problem: Breakpoint not hit**
- **Cause**: File path or line number incorrect
- **Solution**: Use absolute paths or verify relative paths

### 🔍 Forward Trace Success Patterns

**Effective Step Counts:**
- **Simple issues**: `--steps=5-10`  
- **Complex flows**: `--steps=15-25`
- **Avoid**: `--steps=50+` (too much noise)

**Optimal Breakpoint Strategy:**
```bash
# Target specific problem lines
--break="src/SemanticLog/Logger.php:47"  # beAttribute generation
--break="tests/SemanticLog/SchemaComplianceTest.php:48"  # class instantiation
```

**Variable Condition Examples:**
```bash
# Stop when variable has unexpected value
--break="Logger.php:47:\$becoming!=null"
--break="SchemaComplianceTest.php:48:\$class!=null"
```

### 💡 Forward Trace Analysis Tips

**1. Variable State Tracking**
- Focus on `"recording_type": "diff"` entries
- Look for unexpected class names in `$class` variables
- Track namespace resolution in ClassLoader steps

**2. Execution Flow Understanding**
- Step 1-3: Usually setup/initialization
- Critical steps: Where business logic executes
- Look for `ClassLoader->loadClass` calls for namespace issues

**3. Problem Identification**
- **Expected vs Actual values**: Compare variable contents
- **Class resolution**: Check `$logicalPathPsr4` for wrong paths  
- **Missing files**: ClassLoader steps that fail

### 🚀 Forward Trace Power Examples

**Example 1: Namespace Resolution Debugging**
```bash
./vendor/bin/xdebug-debug --context="Debugging FakeProcessedData namespace resolution" --break="tests/SemanticLog/SchemaComplianceTest.php:48" --exit-on-break --steps=10 --json -- php vendor/bin/phpunit --filter testOpenContextSchemaCompliance tests/SemanticLog/SchemaComplianceTest.php
```

**Key Insights from Variables:**
- `$class = "Be\\Framework\\SemanticLog\\FakeProcessedData"` (wrong namespace)
- `$logicalPathPsr4 = "Be/Framework/SemanticLog/FakeProcessedData.php"` (wrong path)

**Example 2: Variable Value Tracing**
Look for variable progression showing the problem:
```json
{"$class": "string: Be\\Framework\\SemanticLog\\FakeProcessedData"}
{"$logicalPathPsr4": "string: Be/Framework/SemanticLog/FakeProcessedData.php"}
```

### ⚡ Quick Troubleshooting

**No output from xdebug-debug?**
1. Test base command: `php vendor/bin/phpunit --filter testMethod file.php`
2. Check phpunit syntax: Use `--filter` not `::`
3. Verify file paths exist

**Breakpoint not hitting?**
1. Use absolute file paths
2. Confirm line number exists
3. Add `--exit-on-break` flag

**Too much/little information?**
1. Adjust `--steps` count (10-20 optimal)
2. Use specific breakpoints
3. Focus on `"recording_type": "diff"`

### 🎯 Forward Trace vs Traditional Debugging

**Forward Trace Advantages:**
- ✅ Real runtime variable values
- ✅ Complete execution flow tracking  
- ✅ Step-by-step state changes
- ✅ No code modification needed
- ✅ Exact problem location identification

**Traditional var_dump Disadvantages:**
- ❌ Requires code modification
- ❌ Shows only single point in time
- ❌ Can be accidentally committed
- ❌ Doesn't show execution flow
- ❌ Based on guesswork placement

**Forward Trace transforms debugging from guesswork to intelligence.**

## Development Hooks

### Automatic Code Style Fixing

After modifying any PHP files, always run code style fixes:

```bash
composer cs-fix
```

**IMPORTANT**: Set up your editor or IDE to automatically run `composer cs-fix` after file saves, or manually run it after each modification session.

Available code quality commands:
- `composer cs` - Check coding style  
- `composer cs-fix` - Fix coding style automatically
- `composer sa` - Run static analysis (phpstan + psalm)
- `composer test` - Run unit tests
- `composer tests` - Run full quality checks (cs + sa + test)