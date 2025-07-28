GRANT SELECT ON performance_schema.table_io_waits_summary_by_index_usage TO 'gazelle'@'%';
GRANT SELECT ON performance_schema.table_io_waits_summary_by_table TO 'gazelle'@'%';
GRANT SELECT ON sys.schema_redundant_indexes TO 'gazelle'@'%';
GRANT SELECT ON sys.schema_unused_indexes TO 'gazelle'@'%';
GRANT SELECT ON sys.x$schema_flattened_keys TO 'gazelle'@'%';
