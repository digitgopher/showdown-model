library(truncnorm)
library(rjson)
library(RMySQL)
#z=rtruncnorm(10000,a=0, b=20, mean=1.15, sd=1.7)
#hist(z, nclass=100)
#hist(rtruncnorm(10000,a=0, b=20, mean=2.57, sd=2.85), nclass=100)

# Used for db user and pw
args = commandArgs(trailingOnly = TRUE)

#**********************
# Define functions

# Right now static, get from db
# Note this function is not to be used for now - discrete has better theoretical underpinnings.
Continuous <- function(x){
  pit_means <- c(2, 4.5, 5.5, 4, 1.5, 1.8, .65, .05);
  pit_sds <- c(.5, 1.8, 1.8, 1.8, 1, 1.2, .5, .5);
  pit_categories <- c('PU', 'SO', 'GB', 'FB', 'BB', '1B','2B', 'HR')

  bat_means <- c(1.15, 1.77, 1.09, 4.7, 6.6, .41, 1.96, .34, 1.98);
  bat_sds <- c(1, .3, .3, 2, 2, .41, 1, .5, 1.8);
  bat_categories <- c('SO', 'GB', 'FB', 'BB', '1B', '1B+', '2B', '3B', 'HR')

  # Make sure condition are met
  if(length(pit_means) != length(pit_sds) | length(pit_means) != length(pit_categories) |
       length(bat_means) != length(bat_sds) | length(bat_means) != length(bat_categories)){
    "The array lengths are not equal. Must quit."
    q("no")
  } else{
    "Array lengths are good."
  }

  num <- x
  l <- list()
  ll <- list()

  # Add all chart values to list
  for(i in 1:length(bat_categories)){
    l <- c(l, rtruncnorm(num,a=0, b=20, mean=bat_means[i], sd=bat_sds[i]))
  }
  for(i in 1:length(pit_categories)){
    ll <- c(ll, rtruncnorm(num,a=0, b=20, mean=pit_means[i], sd=pit_sds[i]))
  }
  # then format the list: columns are results, rows are batters
  dim(l) <- c(num,length(bat_categories))
  dim(ll) <- c(num,length(pit_categories))
  # turn into matrix that looks exactly the same, but add names
  bat_charts <- matrix(unlist(l), ncol = ncol(l), byrow = FALSE, dimnames=list(NULL, bat_categories))
  pit_charts <- matrix(unlist(ll), ncol = ncol(ll), byrow = FALSE, dimnames=list(NULL, pit_categories))

  remove(l)
  remove(ll)

  # Transform so each chart adds to 20. Now cols are batters, rows are results
  bat_charts <- apply(bat_charts, 1, function(x)(x*20)/sum(x))
  pit_charts <- apply(pit_charts, 1, function(x)(x*20)/sum(x))

  # see what statistics are on transformed data, analyze later
  bsds <- apply(bat_charts,1,sd)
  bavgs <- rowMeans(bat_charts)
  psds <- apply(pit_charts,1,sd)
  pavgs <- rowMeans(pit_charts)

  #Get ob/con values, since we can't handle them in the matrix already created
  OB <- rtruncnorm(num,a=0, b=20, mean=7.5, sd=1.4)
  C <- rtruncnorm(num,a=0, b=20, mean=3.1, sd=1.2)

  # insert the ob values back in
  bat_charts <- rbind(OB, bat_charts)
  pit_charts <- rbind(C, pit_charts)

  bat_charts <- toJSON(as.data.frame(t(bat_charts)))
  pit_charts <- toJSON(as.data.frame(t(pit_charts)))

  return(paste(c(bat_charts, pit_charts), collapse=","))
}

Discrete <- function(x){
  batterQuery = "select `value`,
    sum(`colName` = 'SO') as SO,
    sum(`colName` = 'GB') as GB,
    sum(`colName` = 'FB') as FB,
    sum(`colName` = 'BB') as BB,
    sum(`colName` = '1B') as 1B,
    sum(`colName` = '1Bplus') as `1B+`,
    sum(`colName` = '2B') as 2B,
    sum(`colName` = '3B') as 3B,
    sum(`colName` = 'HR') as HR
  from (select 'SO' as `colName`, SO as `value` from `batter-cards` where yearID <= 2001 union all
    select 'GB', GB from `batter-cards` where yearID <= 2001 union all
    select 'FB', FB from `batter-cards` where yearID <= 2001 union all
    select 'BB', BB from `batter-cards` where yearID <= 2001 union all
    select '1B', 1B from `batter-cards` where yearID <= 2001 union all
    select '1Bplus', 1Bplus from `batter-cards` where yearID <= 2001 union all
    select '2B', 2B from `batter-cards` where yearID <= 2001 union all
    select '3B', 3B from `batter-cards` where yearID <= 2001 union all
    select 'HR', HR from `batter-cards` where yearID <= 2001
    ) temp
  group by `value`;"

  pitcherQuery = "select `value`,
       sum(`colName` = 'PU') as PU,
       sum(`colName` = 'SO') as SO,
       sum(`colName` = 'GB') as GB,
       sum(`colName` = 'FB') as FB,
       sum(`colName` = 'BB') as BB,
       sum(`colName` = '1B') as 1B,
       sum(`colName` = '2B') as 2B,
       sum(`colName` = 'HR') as HR
    from (select 'PU' as `colName`, PU as `value` from `pitcher-cards` where yearID <= 2001 union all
      select 'SO', SO from `pitcher-cards` where yearID <= 2001 union all
	    select 'GB', GB from `pitcher-cards` where yearID <= 2001 union all
      select 'FB', FB from `pitcher-cards` where yearID <= 2001 union all
      select 'BB', BB from `pitcher-cards` where yearID <= 2001 union all
      select '1B', 1B from `pitcher-cards` where yearID <= 2001 union all
      select '2B', 2B from `pitcher-cards` where yearID <= 2001 union all
      select 'HR', HR from `pitcher-cards` where yearID <= 2001
      ) temp
    group by `value`;"

  pit_categories <- c('PU', 'SO', 'GB', 'FB', 'BB', '1B','2B', 'HR')
  bat_categories <- c('SO', 'GB', 'FB', 'BB', '1B', '1B+', '2B', '3B', 'HR')

  mydb = dbConnect(MySQL(), user=args[1], password=args[2], dbname=args[3], host='localhost')
  bat_data = dbGetQuery(mydb, batterQuery)
  pit_data = dbGetQuery(mydb, pitcherQuery)
  # Normalize
  bat_data_pct <- apply(bat_data, 2, function(x)(x/sum(x)))
  pit_data_pct <- apply(pit_data, 2, function(x)(x/sum(x)))

  l = matrix(nrow = x, ncol = length(bat_categories), dimnames=list(NULL, bat_categories))
  m = matrix(nrow = x, ncol = length(pit_categories), dimnames=list(NULL, pit_categories))
  # Populate
  for(i in 1:length(bat_categories)){
    l[,i] <- sample(bat_data[,1],x,replace=TRUE,prob=bat_data_pct[,i+1])
  }
  for(i in 1:length(pit_categories)){
    m[,i] <- sample(pit_data[,1],x,replace=TRUE,prob=pit_data_pct[,i+1])
  }

  # Transform so each chart adds to 20. Now cols are batters, rows are results
  # Some values are increased, some are decreased. Assume they ballance out for now...
  bat_charts <- apply(l, 1, function(x)(x*20)/sum(x))
  pit_charts <- apply(m, 1, function(x)(x*20)/sum(x))

  # check it. They ballance out :)
  bsds <- apply(bat_charts,1,sd)
  bavgs <- rowMeans(bat_charts)
  lavgs = colMeans(l)
  lsds = apply(l,2,sd)

  psds <- apply(pit_charts,1,sd)
  pavgs <- rowMeans(pit_charts)
  mavgs = colMeans(m)
  msds = apply(m,2,sd)

  #Get ob/con values, since we can't handle them in the matrix already created
  batterQuery = "SELECT onbase AS OB, count(*) AS COUNT FROM `batter-cards` WHERE yearID <= 2001 GROUP BY onbase;"
  OB = dbGetQuery(mydb, batterQuery)
  OB = sample(OB[,1],x,replace=TRUE,prob=OB[,2])
  pitcherQuery = "SELECT control AS C, count(*) AS COUNT FROM `pitcher-cards` WHERE yearID <= 2001 GROUP BY control;"
  C = dbGetQuery(mydb, pitcherQuery)
  C = sample(C[,1],x,replace=TRUE,prob=C[,2])

  # insert the ob values back in
  bat_charts <- rbind(OB, bat_charts)
  pit_charts <- rbind(C, pit_charts)

  bat_charts <- toJSON(as.data.frame(t(bat_charts)))
  pit_charts <- toJSON(as.data.frame(t(pit_charts)))

  return(paste(c(bat_charts, pit_charts), collapse=","))
}

#**********************
# Program logic

if(args[4] == 'continuous'){
  Continuous(as.numeric(args[5]))
}else if(args[4] == 'discrete'){
  Discrete(as.numeric(args[5]))
} else{
  "args[4] can be either continuous or discrete"
}

