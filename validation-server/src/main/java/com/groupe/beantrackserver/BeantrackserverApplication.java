package com.groupe.beantrackserver;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.scheduling.annotation.EnableAsync;

@SpringBootApplication
@EnableAsync
public class BeantrackserverApplication {

	public static void main(String[] args) {
		SpringApplication.run(BeantrackserverApplication.class, args);
	}

}
