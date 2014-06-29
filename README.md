WebProfilerExtension
==================

## Installation

Clone into phpBB/ext/nicofuma/webprofiler:

    git clone https://github.com/Nicofuma/WebProfilerExtension.git phpBB/ext/nicofuma/webprofiler
    
Go to "ACP" > "Customise" > "Extensions" and enable the "phpBB 3.1 NV Newspage Extension" extension.

### Enable the timeline

If you want to use use the timeline:

1. Move `ext/nicofuma/webprofiler/vendor/symfony/stopwatch/Symfony` to `ext/Symfony`
2. In `ext/nicofuma/webprofiler/config/profiler.yml` replace
```
imports:
    - { resource: event_dispatcher.yml }
```
with
```
imports:
    - { resource: event_dispatcher_stopwatch.yml }
```
3. Enable the extension in the acp

## How to use

When you access to a page through the app.php script (ie: a page displayed by an extension) a report is generated and stored in a file.
Then you can access to these reports on `app.php/_profiler/`

## TODO

- [x] POC
- [ ] Design
- [x] List of available reports
- [ ] Toolbar
- [x] Menu
- [x] Content
- [x] Search
- [x] Admin
    - [x] Purge
    - [x] Export
    - [x] Import
- [ ] Additionals reports
    - [ ] Better events
    - [ ] Better timeline
    - [ ] Included files
    - [ ] Time per event / listener
    - [ ] SQL report
